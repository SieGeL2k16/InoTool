<?php declare(strict_types=1);
/**
 * Handles invoices
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (23-Apr-2023)
 */
namespace App\Controller\FreelancerManager;

use App\Entity\FlInvoices;
use App\Entity\User;
use App\Repository\FlCustomerRepository;
use App\Repository\FlInvoicesRepository;
use App\Repository\FlProjectsRepository;
use App\Service\AppConfigHelper;
use App\Service\BlobSupport;
use App\Service\globalHelper;
use App\Twig\AppRuntime;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Twig\Error\RuntimeError;
use Twig\Extra\Intl\IntlExtension;

#[IsGranted('ROLE_USER')]
#[Route("/freelancer/invoices/")]
class InvoicesController extends AbstractController
  {
  const ACTNAV = 'invoices';
  
  /** Filter definitions */
  const CFG_FILTER_TYPE = 'fl.invoices.type';
  const CFG_FILTER_PAID = 'fl.invoices.paid';

  /** @var LoggerInterface $logger */
  private LoggerInterface $logger;

  /** @var AppConfigHelper $configHelper */
  private AppConfigHelper $configHelper;
  
  /** @var EntityManagerInterface $em */
  private EntityManagerInterface $em;
  
  /**
   * @param LoggerInterface $logger
   * @param AppConfigHelper $configHelper
   * @param EntityManagerInterface $em
   */
  public function __construct(LoggerInterface $logger,AppConfigHelper $configHelper,EntityManagerInterface $em)
    {
    $this->logger = $logger;
    $this->configHelper = $configHelper;
    $this->em = $em;
    }
  
  /**
   * Shows all invoices via DataTables.
   * @return Response
   */
  #[Route("list",name: "fl_invoices_list")]
  public function list():Response
    {
    /** @var User $user */
    $user = $this->getUser();
    $config = $this->configHelper->xGet([self::CFG_FILTER_TYPE,self::CFG_FILTER_PAID],$user);
    return $this->render('freelancermanager/invoices_list.html.twig',[
      'ACTNAV'        => self::ACTNAV,
      'TOTALS'        => $this->em->getRepository(FlInvoices::class)->getTotals($user->getId()),
      'INVOICE_TYPES' => FlInvoices::$_INVOICE_TYPE_LIST,
      'F_TYPE'        => $config[self::CFG_FILTER_TYPE],
      'F_PAID'        => $config[self::CFG_FILTER_PAID],
      ]);
    }
  
  /**
   * Ajax Datatables backend.
   * NOTE: Some columns are defined here instead of using DataTables' render function!
   * @param Request $request
   * @param Environment $twig
   * @param AppRuntime $app
   * @return JsonResponse
   * @throws RuntimeError
   */
  #[Route("ajax",name: "fl_invoices_ajax",methods: ["POST"])]
  public function list_ajax(Request $request,Environment $twig,AppRuntime $app):JsonResponse
    {
    /** @var User $user */
    $user     = $this->getUser();
    $colcount = count($request->get('columns'));
    $draw     = $request->get('draw');      // Draw counter for datatable rendering
    $f_type   = $request->get('F_TYPE');
    $f_paid   = $request->get('F_PAID');
    $this->configHelper->Set(self::CFG_FILTER_TYPE,(string)$f_type,$user);
    $this->configHelper->Set(self::CFG_FILTER_PAID,(string)$f_paid,$user);
    $params = [
      'START'     => (int)$request->get('start'),
      'LIMIT'     => (int)$request->get('length'),
      'SEARCH'    => $request->get('search')['value'] ?? '',
      'ORDER'     => globalHelper::parseDtOrder($request->get('order'),$colcount),
      'F_TYPE'    => $f_type,
      'F_PAID'    => $f_paid,
      ];
    $data  = [];
    $dummy = $this->em->getRepository(FlInvoices::class)->getDataTablesValues($params,$user->getId());
    //  0         1              2             3           4            5        6         7
    //  id,invoice_number,invoice_date,customer_name,invoice_type,invoice_fee,is_paid,pdf_filename
    $intl = new IntlExtension();
    foreach($dummy['DATA'] as $d)
      {
      $d[2] = $intl->formatDate($twig, $d[2]);
      $d[4] = FlInvoices::$_INVOICE_TYPE_LIST[$d[4]];
      $d[5] = $intl->formatCurrency($d[5],'EUR');
      $d[6] = $app->YesNo($d[6]);
      $data[] = $d;
      }
    $total = $this->em->getRepository(FlInvoices::class)->count(['RefUser' => $user]);
    return new JsonResponse([
      'draw'            => (int)$draw,
      'recordsTotal'    => $total,
      'recordsFiltered' => $dummy['TOTAL'],
      'data'            => $data,
      ]);
    }
  
  /**
   * Downloads a given file
   * @param BlobSupport $blobSupport
   * @param int $id
   * @return Response
   */
  #[Route("download/{id}",name: "fl_invoices_dl")]
  public function download(BlobSupport $blobSupport,int $id = 0):Response
    {
    $user = $this->getUser();
    return $blobSupport->downloadInvoice($id,$user);
    }
  
  /**
   * Either Add or edit an invoice
   * @param int $id
   * @return Response
   */
  #[Route("form/{id}",name: "fl_invoices_form")]
  public function form(FlInvoicesRepository $flInvoicesRepository,FlCustomerRepository $customerRepository,int $id = 0):Response
    {
    $user = $this->getUser();
    if($id)
      {
      $PAGE_TITLE = "Rechnung #$id bearbeiten";
      $invoice = $flInvoicesRepository->findOneBy(['id' => $id,'RefUser' => $user]);
      if($invoice === null)
        {
        $this->logger->warning(__METHOD__.": No entry found with ID=$id - cannot edit!");
        $this->addFlash("warning","Keinen Eintrag zum Bearbeiten gefunden?!");
        return $this->redirectToRoute("fl_invoices_list");
        }
      }
    else
      {
      $PAGE_TITLE = "Neue Rechnung erstellen";
      $invoice = new FlInvoices();
      }
    $customer_list = $customerRepository->findBy(['RefUser' => $user],['Name' => 'asc']);
    return $this->render("freelancermanager/invoices_form.html.twig", [
      'ACTNAV'        => self::ACTNAV,
      'PAGE_TITLE'    => $PAGE_TITLE,
      'INVOICE'       => $invoice,
      'CUSTOMER_LIST' => $customer_list,
      'INVOICE_TYPES' => FlInvoices::$_INVOICE_TYPE_LIST,
      ]);
    }

  /**
   * Saves form data.
   * @param Request $request
   * @param FlCustomerRepository $customerRepository
   * @return Response
   */
  #[Route("save",name: "fl_invoices_save",methods: ['POST'])]
  public function save(Request $request,FlCustomerRepository $customerRepository):Response
    {
    /** @var User $user */
    $user = $this->getUser();
    $id   = (int)$request->get('PID',0);
    if($id)
      {
      $invoice = $this->em->getRepository(FlInvoices::class)->findOneBy(['id' => $id,'RefUser' => $user]);
      if($invoice === null)
        {
        $this->logger->warning(__METHOD__.": No entry found with ID=$id - cannot save!");
        $this->addFlash("warning","Keinen Eintrag zum Speichern gefunden?!");
        return $this->redirectToRoute("fl_invoices_list");
        }
      $invoice->setLastModifiedOn(new DateTime());
      $logsuffix = 'updated.';
      }
    else
      {
      $invoice = (new FlInvoices())->setRefUser($user)->setCreatedOn(new DateTimeImmutable());
      $logsuffix = 'created.';
      }
    $customer = $customerRepository->findOneBy(['id' => (int)$request->get('RefCustomerId'),'RefUser' => $user]);
    $inv_date = DateTime::createFromFormat('!Y-m-d',(string)$request->get('inv_date'));
    $netto    = $this->parseAmount($request->get('inv_netto'));
    $brutto   = $this->parseAmount($request->get('inv_brutto'));
    $tax      = $this->parseAmount($request->get('inv_tax'));
    $inv_type = (int)$request->get('inv_type');
    $inv_nr   = trim((string)$request->get('inv_nr'));
    if($customer === null || $inv_date === false || $inv_nr === '' || $netto === null || $brutto === null || $tax === null || !isset(FlInvoices::$_INVOICE_TYPE_LIST[$inv_type]))
      {
      $this->addFlash("warning","Ungültige Eingaben - Rechnung nicht gespeichert!");
      return $this->redirectToRoute("fl_invoices_form",['id' => $id]);
      }
    /** @var UploadedFile|null $pdf */
    $pdf = $request->files->get('inv_pdf');
    if($pdf !== null)
      {
      if(!$pdf->isValid())
        {
        $this->logger->error(__METHOD__.": Upload failed: ".$pdf->getErrorMessage());
        $this->addFlash("danger","Upload fehlgeschlagen: ".$pdf->getErrorMessage());
        return $this->redirectToRoute("fl_invoices_form",['id' => $id]);
        }
      if($pdf->getMimeType() !== 'application/pdf')
        {
        $this->addFlash("warning","Nur PDF-Dateien erlaubt - Rechnung nicht gespeichert!");
        return $this->redirectToRoute("fl_invoices_form",['id' => $id]);
        }
      $invoice
        ->setPdfData(file_get_contents($pdf->getRealPath()))
        ->setPdfFilename($pdf->getClientOriginalName())
        ->setPdfFilesize($pdf->getSize())
        ->setPdfMimeType('application/pdf')
        ;
      }
    elseif((bool)$request->get('inv_pdf_delete'))
      {
      $invoice
        ->setPdfData(null)
        ->setPdfFilename(null)
        ->setPdfFilesize(null)
        ->setPdfMimeType(null)
        ;
      }
    $invoice
      ->setRefCustomer($customer)
      ->setInvoiceDate($inv_date)
      ->setInvoiceNumber($inv_nr)
      ->setInvoiceType($inv_type)
      ->setIsPaid((bool)$request->get('ispaid'))
      ->setNoTax((bool)$request->get('inv_notax'))
      ->setSumNetto($netto)
      ->setSumBrutto($brutto)
      ->setTaxPct($tax)
      ->setComment($request->get('inv_comment'))
      ;
    $this->em->persist($invoice);
    $this->em->flush();
    $this->addFlash('success',"Rechnung {$invoice->getInvoiceNumber()} gespeichert.");
    $this->logger->info("Invoice {$invoice->getInvoiceNumber()} successfully $logsuffix");
    return $this->redirectToRoute('fl_invoices_list');
    }

  /**
   * Removes an invoice from database.
   * @param Request $request
   * @return Response
   */
  #[Route("delete",name: "fl_invoices_delete",methods: ['POST'])]
  public function RemoveInvoice(Request $request):Response
    {
    $id = (int)$request->get('PID');
    $invoice = $this->em->getRepository(FlInvoices::class)->findOneBy(['id' => $id,'RefUser' => $this->getUser()]);
    if($invoice === null)
      {
      $this->logger->warning(__METHOD__.": No invoice found with ID=$id - delete not possible!");
      $this->addFlash('warning',"Rechnung nicht gefunden - Löschen nicht möglich!");
      return $this->redirectToRoute('fl_invoices_list');
      }
    $this->em->remove($invoice);
    $this->em->flush();
    $this->logger->info("Invoice {$invoice->getInvoiceNumber()} successfully removed");
    $this->addFlash('success',"Rechnung {$invoice->getInvoiceNumber()} erfolgreich gelöscht.");
    return $this->redirectToRoute('fl_invoices_list');
    }

  /**
   * Converts amount with comma or dot as decimal separator.
   * @param mixed $value
   * @return string|null NULL if invalid
   */
  private function parseAmount(mixed $value):?string
    {
    $value = str_replace(',','.',trim((string)$value));
    if(!is_numeric($value))
      {
      return null;
      }
    return sprintf("%.2f",(float)$value);
    }
  }
