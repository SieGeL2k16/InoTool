<?php declare(strict_types=1);
/**
 * Project related methods.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (04-Dez-2022)
 */

namespace App\Controller\FreelancerManager;

use App\Repository\FlProjectsRepository;
use App\Service\AppConfigHelper;
use App\Service\globalHelper;
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
      'ACTNAV'    => self::ACTNAV,
      //'F_ACTIVE'  => $this->configHelper->Get(self::CFG_FILTER_ACTIVE,'',$this->getUser()),
    ]);
    }
  
  /**
   * AJAX backend for customer list
   * @param Request $request
   * @param FlProjectsRepository $flProjectsRepository
   * @return JsonResponse
   */
  #[Route("ajax",name: "fl_projects_ajax")]
  public function list_ajax(Request $request,FlProjectsRepository $flProjectsRepository):JsonResponse
    {
    $user     = $this->getUser();
    $colcount = count($request->get('columns'));
    $draw     = $request->get('draw');      // Draw counter for datatable rendering
    $f_active = $request->get('F_ACTIVE');
    //$this->configHelper->Set(self::CFG_FILTER_ACTIVE,$f_active,$user);
    $params   = [
      'START'     => (int)$request->get('start'),
      'LIMIT'     => (int)$request->get('length'),
      'SEARCH'    => $request->get('search')['value'] ?? '',
      'ORDER'     => globalHelper::parseDtOrder($request->get('order'),$colcount),
      'F_ACTIVE'  => $f_active,
      ];
    $data     = ['TOTAL' => 0,'DATA' => []];//$customerRepository->GetDataTablesValues($params,$user->getId(),$colcount);
    $total    = 0;//$customerRepository->count(['RefUser' => $user]);
    return new JsonResponse([
      'draw'            => (int)$draw,
      'recordsTotal'    => $total,
      'recordsFiltered' => $data['TOTAL'],
      'data'            => $data['DATA'],
      ]);
    }
  }
