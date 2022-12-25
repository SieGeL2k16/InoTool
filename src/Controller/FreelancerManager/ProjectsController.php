<?php declare(strict_types=1);
/**
 * Project related methods.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (04-Dez-2022)
 */

namespace App\Controller\FreelancerManager;

use App\Entity\FlCustomer;
use App\Entity\FlProjects;
use App\Repository\FlCustomerRepository;
use App\Repository\FlProjectsRepository;
use App\Service\AppConfigHelper;
use App\Service\globalHelper;
use DateTime;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_USER')]
#[Route("/freelancer/projects/")]
class ProjectsController extends AbstractController
  {
  const ACTNAV = 'projects';
  
  const CFG_FILTER_STATUS     = 'fl.projects.status';
  const CFG_FILTER_REPORTING  = 'fl.projects.reporting';
  const CFG_FILTER_CUSTOMER   = 'fl.projects.custid';
  
  /** @var LoggerInterface $logger */
  private LoggerInterface $logger;
  
  /** @var AppConfigHelper $configHelper */
  private AppConfigHelper $configHelper;
  
  public function __construct(LoggerInterface $logger,AppConfigHelper $configHelper)
    {
    $this->logger = $logger;
    $this->configHelper = $configHelper;
    }
  
  /**
   * Lists all customer currently registered for current user.
   * @param FlCustomerRepository $customerRepository
   * @return Response
   */
  #[Route("list",name: "fl_projects_list")]
  public function list(FlCustomerRepository $customerRepository):Response
    {
    $user   = $this->getUser();
    $config = $this->configHelper->xGet([self::CFG_FILTER_CUSTOMER,self::CFG_FILTER_REPORTING,self::CFG_FILTER_STATUS],$user);
    $customer_list = $customerRepository->findBy(['RefUser' => $user,'Active' => true],['Name' => 'asc']);
    return $this->render('freelancermanager/projects_list.html.twig',[
      'ACTNAV'        => self::ACTNAV,
      'F_STATUS'      => $config[self::CFG_FILTER_STATUS],
      'F_REPORTING'   => $config[self::CFG_FILTER_REPORTING],
      'F_CUSTOMER'    => $config[self::CFG_FILTER_CUSTOMER],
      'CUSTOMER_LIST' => $customer_list,
      ]);
    }
  
  /**
   * AJAX backend for projects list
   * @param Request $request
   * @param FlProjectsRepository $flProjectsRepository
   * @return JsonResponse
   * @throws Exception
   */
  #[Route("ajax",name: "fl_projects_ajax")]
  public function list_ajax(Request $request,FlProjectsRepository $flProjectsRepository):JsonResponse
    {
    $user         = $this->getUser();
    $colcount     = count($request->get('columns'));
    $draw         = $request->get('draw');      // Draw counter for datatable rendering
    $f_status     = $request->get('F_STATUS');
    $f_reporting  = $request->get('F_REPORTING');
    $f_customer   = $request->get('F_CUSTOMER');
    $this->configHelper->Set(self::CFG_FILTER_STATUS,$f_status,$user);
    $this->configHelper->Set(self::CFG_FILTER_REPORTING,$f_reporting,$user);
    $this->configHelper->Set(self::CFG_FILTER_CUSTOMER,$f_customer,$user);
    $params   = [
      'START'       => (int)$request->get('start'),
      'LIMIT'       => (int)$request->get('length'),
      'SEARCH'      => $request->get('search')['value'] ?? '',
      'ORDER'       => globalHelper::parseDtOrder($request->get('order'),$colcount),
      'F_STATUS'    => $f_status,
      'F_REPORTING' => $f_reporting,
      'F_CUSTOMER'  => $f_customer,
      ];
    $data     = $flProjectsRepository->GetDataTablesValues($params,$user->getId(),$colcount);
    $total    = $flProjectsRepository->count(['RefUser' => $user]);
    return new JsonResponse([
      'draw'            => (int)$draw,
      'recordsTotal'    => $total,
      'recordsFiltered' => $data['TOTAL'],
      'data'            => $data['DATA'],
      ]);
    }
  
  /**
   * Add/modify project entry
   * @param FlProjectsRepository $flProjectsRepository
   * @param FlCustomerRepository $customerRepository
   * @param int $id
   * @return Response
   */
  #[Route("form/{id}",name: "fl_projects_form")]
  public function form(FlProjectsRepository $flProjectsRepository,
                       FlCustomerRepository $customerRepository, int $id = 0):Response
    {
    $user = $this->getUser();
    $customer_list = $customerRepository->findBy(['RefUser' => $user,'Active' => true],['Name' => 'asc']);
    if(count($customer_list) === 0)
      {
      $this->addFlash('warning',"Es muss zuerst mind. ein Kunde eingetragen sein!");
      return $this->redirectToRoute('fl_customer_form');
      }
    if($id)
      {
      $project = $flProjectsRepository->findOneBy(['RefUser' => $user,'id' => $id]);
      if($project === null)
        {
        $this->addFlash('warning',"Projekt mit ID = $id nicht gefunden?");
        $this->logger->warning(__METHOD__.": Project with ID=$id not found - cannot edit?!");
        return $this->redirectToRoute('fl_projects_list');
        }
      $page_title = 'Bearbeite "'.$project->getProjectName().'"';
      }
    else
      {
      $project = (new FlProjects())->setRefUser($user);
      $page_title = "Neues Projekt anlegen";
      }
    return $this->render('freelancermanager/projects_form.html.twig',[
      'ACTNAV'        => self::ACTNAV,
      'PROJECT'       => $project,
      'PAGE_TITLE'    => $page_title,
      'CUSTOMER_LIST' => $customer_list,
      ]);
    }
  
  /**
   * Either adds or updates a project entry.
   * @param Request $request
   * @param EntityManagerInterface $entity
   * @return Response
   */
  #[Route("save",name: "fl_projects_save",methods: ['POST'])]
  public function save(Request $request, EntityManagerInterface $entity):Response
    {
    $user = $this->getUser();
    $id   = (int)$request->get('PID',0);
    if($id)
      {
      $project = $entity->getRepository(FlProjects::class)->findOneBy(['RefUser' => $user,'id' => $id]);
      if($project === null)
        {
        $this->addFlash('warning',"Projekt mit ID = $id nicht gefunden?");
        $this->logger->warning(__METHOD__.": Project with ID=$id not found - cannot save?!");
        return $this->redirectToRoute('fl_projects_list');
        }
      $project->setLastModifiedOn(new DateTime());
      $logsuffix = 'updated.';
      }
    else
      {
      $project = (new FlProjects())->setRefUser($user);
      $logsuffix = 'created.';
      }
    $project->setRefCustomer($entity->getRepository(FlCustomer::class)->findOneBy(['RefUser' => $user, 'id' => (int)$request->get('RefCustomerId')]))
      ->setProjectName($request->get('projectName'))
      ->setDescription($request->get('description'))
      ->setStatus((int)$request->get('status'))
      ->setNoReporting((bool)$request->get('noReporting'))
      ->setWorkUnit((int)$request->get('workUnit'))
      ->setPayPerWorkUnit((int)$request->get('payPerWorkUnit'))
      ->setCurrency($request->get('currency'))
      ->setTimeBudget((int)$request->get('timeBudget'))
      ;
    $entity->persist($project);
    $entity->flush();
    $this->addFlash('success',"Projekt {$project->getProjectName()} gespeichert.");
    $this->logger->info("Project \"{$project->getProjectName()}\" successfully $logsuffix");
    return $this->redirectToRoute('fl_projects_list');
    }
  
  /**
   * Removes a project from database.
   * @param Request $request
   * @param EntityManagerInterface $em
   * @return Response
   */
  #[Route("delete",name: "fl_projects_delete",methods: ['POST'])]
  public function RemoveProject(Request $request,EntityManagerInterface $em):Response
    {
    $pid = (int)$request->get('PID');
    $project = $em->getRepository(FlProjects::class)->findOneBy(['id' => $pid, 'RefUser' => $this->getUser()]);
    if($project === null)
      {
      $this->logger->warning(__METHOD__.": No project found with ID=$pid - delete not possible!");
      $this->addFlash('warning',"Project nicht gefunden - Löschen nicht möglich!");
      return $this->redirectToRoute('fl_projects_list');
      }
    $em->remove($project);
    $em->flush();
    $this->logger->info("Project \"{$project->getProjectName()}\" successfully removed");
    $this->addFlash('success',"Projekt {$project->getProjectName()} erfolgreich gelöscht.");
    return $this->redirectToRoute('fl_projects_list');
    }
  
  }
