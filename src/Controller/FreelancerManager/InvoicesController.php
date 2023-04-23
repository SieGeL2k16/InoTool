<?php declare(strict_types=1);
/**
 * Handles invoices
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (23-Apr-2023)
 */
namespace App\Controller\FreelancerManager;

use App\Entity\FlInvoices;
use App\Service\AppConfigHelper;
use App\Service\globalHelper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

#[IsGranted('ROLE_USER')]
#[Route("/freelancer/invoices/")]
class InvoicesController extends AbstractController
  {
  const ACTNAV = 'invoices';
  
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
    $user = $this->getUser();
    return $this->render('freelancermanager/invoices_list.html.twig',[
      'ACTNAV'  => self::ACTNAV,
      'TOTALS'  => $this->em->getRepository(FlInvoices::class)->getTotals($user->getId())
      ]);
    }
  
  /**
   * Ajax Datatables backend.
   * @param Request $request
   * @return JsonResponse
   */
  #[Route("ajax",name: "fl_invoices_ajax")]
  public function list_ajax(Request $request):JsonResponse
    {
    $user     = $this->getUser();
    $colcount = count($request->get('columns'));
    $draw     = $request->get('draw');      // Draw counter for datatable rendering
    $params   = [
      'START'     => (int)$request->get('start'),
      'LIMIT'     => (int)$request->get('length'),
      'SEARCH'    => $request->get('search')['value'] ?? '',
      'ORDER'     => globalHelper::parseDtOrder($request->get('order'),$colcount),
      ];
    $data     = $this->em->getRepository(FlInvoices::class)->GetDataTablesValues($params,$user->getId(),$colcount);
    $total    = $this->em->getRepository(FlInvoices::class)->count(['RefUser' => $user]);
    return new JsonResponse([
      'draw'            => (int)$draw,
      'recordsTotal'    => $total,
      'recordsFiltered' => $data['TOTAL'],
      'data'            => $data['DATA'],
      ]);
    }
  }
