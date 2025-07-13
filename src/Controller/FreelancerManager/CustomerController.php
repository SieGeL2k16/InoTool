<?php declare(strict_types=1);
/**
 * All customer related methods.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (13-Nov-2022)
 */

namespace App\Controller\FreelancerManager;

use App\Entity\FlCustomer;
use App\Entity\User;
use App\Repository\FlCustomerRepository;
use App\Service\AppConfigHelper;
use App\Service\globalHelper;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route("/freelancer/customer/")]
class CustomerController extends AbstractController
  {
  const ACTNAV = 'customer';

  /** @var string Key for filter configuration */
  const CFG_FILTER_ACTIVE = 'fl.customer.active';

  /** @var LoggerInterface $logger */
  private LoggerInterface $logger;

  /** @var AppConfigHelper $configHelper */
  private AppConfigHelper $configHelper;

  /**
   * @param LoggerInterface $logger
   * @param AppConfigHelper $configHelper
   */
  public function __construct(LoggerInterface $logger,AppConfigHelper $configHelper)
    {
    $this->logger = $logger;
    $this->configHelper = $configHelper;
    }

  /**
   * Lists all customer currently registered for current user.
   * @return Response
   */
  #[Route("list",name: "fl_customer_list")]
  public function list():Response
    {
    return $this->render('freelancermanager/customer_list.html.twig',[
      'ACTNAV'    => self::ACTNAV,
      'F_ACTIVE'  => $this->configHelper->Get(self::CFG_FILTER_ACTIVE,'',$this->getUser()),
      ]);
    }

  /**
   * AJAX backend for customer list
   * @param Request $request
   * @param FlCustomerRepository $customerRepository
   * @return JsonResponse
   * @throws Exception
   */
  #[Route("ajax",name: "fl_customer_ajax")]
  public function list_ajax(Request $request,FlCustomerRepository $customerRepository):JsonResponse
    {
    /** @var User $user */
    $user     = $this->getUser();
    $colcount = count($request->get('columns'));
    $draw     = $request->get('draw');      // Draw counter for datatable rendering
    $f_active = $request->get('F_ACTIVE');
    $this->configHelper->Set(self::CFG_FILTER_ACTIVE,$f_active,$user);
    $params   = [
      'START'     => (int)$request->get('start'),
      'LIMIT'     => (int)$request->get('length'),
      'SEARCH'    => $request->get('search')['value'] ?? '',
      'ORDER'     => globalHelper::parseDtOrder($request->get('order'),$colcount),
      'F_ACTIVE'  => $f_active,
      ];
    $data     = $customerRepository->GetDataTablesValues($params,$user->getId(),$colcount);
    $total    = $customerRepository->count(['RefUser' => $user]);
    return new JsonResponse([
      'draw'            => (int)$draw,
      'recordsTotal'    => $total,
      'recordsFiltered' => $data['TOTAL'],
      'data'            => $data['DATA'],
      ]);
    }

  /**
   * HTML formular to add or modify customer data.
   * @param FlCustomerRepository $customerRepository
   * @param int $id 0 => New customer, else customer id to modify
   * @return Response
   * @throws Exception
   */
  #[Route("form/{id}",name: "fl_customer_form")]
  public function form(FlCustomerRepository $customerRepository, int $id = 0):Response
    {
    $user = $this->getUser();
    if($id)
      {
      $customer = $customerRepository->findOneBy(['RefUser' => $user,'id' => $id]);
      if($customer === null)
        {
        $this->addFlash('warning',"Kunde mit ID = $id nicht gefunden?");
        $this->logger->warning(__METHOD__.": Customer with ID=$id not found - cannot edit?!");
        return $this->redirectToRoute('fl_customer_list');
        }
      $page_title = 'Bearbeite "'.$customer->getName().'"';
      }
    else
      {
      $customer = (new FlCustomer())
        ->setRefUser($user)
        ->setCustomerNumber(sprintf("%s",$customerRepository->GetNextCustomerNumber($user)))
        ->setActive(true)
        ;
      $page_title = "Neuen Kunden anlegen";
      }
    return $this->render('freelancermanager/customer_form.html.twig',[
      'ACTNAV'      => self::ACTNAV,
      'CUSTOMER'    => $customer,
      'PAGE_TITLE'  => $page_title,
      ]);
    }

  /**
   * Saves form data.
   * @param Request $request
   * @param EntityManagerInterface $entity
   * @return Response
   */
  #[Route("save",name: "fl_customer_save",methods: ['POST'])]
  public function save(Request $request, EntityManagerInterface $entity):Response
    {
    $user= $this->getUser();
    $id = $request->get('CUSTID',0);
    if($id)
      {
      $customer = $entity->getRepository(FlCustomer::class)->findOneBy(['RefUser' => $user,'id' => $id]);
      if($customer === null)
        {
        $this->addFlash('warning',"Kunde mit ID = $id nicht gefunden?");
        $this->logger->warning(__METHOD__.": Customer with ID=$id not found - cannot save?!");
        return $this->redirectToRoute('fl_customer_list');
        }
      $customer->setLastModifiedOn(new DateTime());
      $logsuffix = 'updated.';
      }
    else
      {
      $customer = (new FlCustomer())->setRefUser($user)->setCreatedOn(new DateTimeImmutable());
      $logsuffix = 'created.';
      }
    $customer->setCustomerNumber($request->get('customerNumber'))
      ->setActive((bool)$request->get('active'))
      ->setName($request->get('name'))
      ->setStreet($request->get('street'))
      ->setPostalCode($request->get('postalCode'))
      ->setCity($request->get('city'))
      ->setCountry($request->get('country'))
      ->setCustomerUrl($request->get('customerUrl'))
      ->setContactName($request->get('contactName'))
      ->setContactEmail($request->get('contactEmail'))
      ->setContactPosition($request->get('contactPosition'))
      ->setPhoneBusiness($request->get('phoneBusiness'))
      ->setPhoneFax($request->get('phoneFax'))
      ->setPhoneMobile($request->get('phoneMobile'))
      ->setPhonePrivate($request->get('phonePrivate'))
      ;
    $entity->persist($customer);
    $entity->flush();
    $this->addFlash('success',"Kunde {$customer->getName()} gespeichert.");
    $this->logger->info("Customer {$customer->getName()} successfully $logsuffix");
    return $this->redirectToRoute('fl_customer_list');
    }

  /**
   * Removes a customer from database.
   * @param Request $request
   * @param EntityManagerInterface $em
   * @return Response
   */
  #[Route("delete",name: "fl_customer_delete",methods: ['POST'])]
  public function RemoveCustomer(Request $request,EntityManagerInterface $em):Response
    {
    $custid = (int)$request->get('CUSTID');
    $customer = $em->getRepository(FlCustomer::class)->findOneBy(['id' => $custid, 'RefUser' => $this->getUser()]);
    if($customer === null)
      {
      $this->logger->warning(__METHOD__.": No customer found with ID=$custid - delete not possible!");
      $this->addFlash('warning',"Kunde nicht gefunden - Löschen nicht möglich!");
      return $this->redirectToRoute('fl_customer_list');
      }
    $em->remove($customer);
    $em->flush();
    $this->logger->info("Customer {$customer->getName()} successfully removed");
    $this->addFlash('success',"Kunde {$customer->getName()} erfolgreich gelöscht.");
    return $this->redirectToRoute('fl_customer_list');
    }

  }
