<?php declare(strict_types=1);
/**
 * Project related methods.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (04-Dez-2022)
 */

namespace App\Controller\FreelancerManager;

use App\Entity\FlProjects;
use App\Repository\FlCustomerRepository;
use App\Repository\FlProjectsRepository;
use App\Service\AppConfigHelper;
use App\Service\globalHelper;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
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
   * @return Response
   */
  #[Route("list",name: "fl_projects_list")]
  public function list():Response
    {
    return $this->render('freelancermanager/projects_list.html.twig',[
      'ACTNAV'        => self::ACTNAV,
      'F_STATUS'      => $this->configHelper->Get(self::CFG_FILTER_STATUS,'',$this->getUser()),
      'F_REPORTING'   => $this->configHelper->Get(self::CFG_FILTER_REPORTING,'',$this->getUser()),
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
    $user     = $this->getUser();
    $colcount = count($request->get('columns'));
    $draw     = $request->get('draw');      // Draw counter for datatable rendering
    $f_status = $request->get('F_STATUS');
    $f_reporting = $request->get('F_REPORTING');
    $this->configHelper->Set(self::CFG_FILTER_STATUS,$f_status,$user);
    $this->configHelper->Set(self::CFG_FILTER_REPORTING,$f_reporting,$user);
    $params   = [
      'START'       => (int)$request->get('start'),
      'LIMIT'       => (int)$request->get('length'),
      'SEARCH'      => $request->get('search')['value'] ?? '',
      'ORDER'       => globalHelper::parseDtOrder($request->get('order'),$colcount),
      'F_STATUS'    => $f_status,
      'F_REPORTING' => $f_reporting,
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
        'CUSTOMER_LIST' => $customerRepository->findBy(['RefUser' => $user,'Active' => true],['Name' => 'asc'])
        ]);
    
    }
  
  }
