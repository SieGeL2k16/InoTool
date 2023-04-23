<?php
/**
 * Startpage with dashboard for Freelancer Manager.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (26-12-2021)
 */

namespace App\Controller\FreelancerManager;

use App\Entity\FlConfiguration;
use App\Entity\FlCustomer;
use App\Entity\FlInvoices;
use App\Entity\FlProjectEntries;
use App\Entity\FlProjects;
use App\Entity\FlServiceContracts;
use App\Service\globalHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class IndexController extends AbstractController
  {
  /** @var EntityManagerInterface $entity */
  private EntityManagerInterface $entity;
  
  /**
   * @param EntityManagerInterface $entity
   */
  public function __construct(EntityManagerInterface $entity)
    {
    $this->entity = $entity;
    }
  
  /**
   * Dashboard page.
   * @param globalHelper $globalHelper
   * @param Request $request
   * @return Response
   */
  #[Route('/freelancer',name: 'fl_index')]
  public function index(globalHelper $globalHelper,Request $request):Response
    {
    $user= $this->getUser();
    $noConfig = false;
    $config = $this->entity->getRepository(FlConfiguration::class)->findOneBy(['RefUser' => $user]);
    if($config === null)
      {
      $noConfig = true;
      $config = new FlConfiguration();
      $config->setRefUser($user);
      }
    $cust_stats = ['ACTIVE' => 0, 'INACTIVE' => 0, 'TOTAL' => 0];
    foreach($this->entity->getRepository(FlCustomer::class)->findBy(['RefUser' => $user]) as $item)
      {
      if($item->isActive() === true)
        {
        $cust_stats['ACTIVE']++;
        }
      else
        {
        $cust_stats['INACTIVE']++;
        }
      $cust_stats['TOTAL']++;
      }
    $labels = $values = [];
    foreach($this->entity->getRepository(FlProjectEntries::class)->getYearlyProfit($user) as $d)
      {
      $labels[] = $d['y'];
      $values[] = $d['sum'];
      }
    $prjcnt = $this->entity->getRepository(FlProjects::class)->count(['RefUser' => $user]);
    $mons = $globalHelper->getLocalizedMonths($request->getLocale());
    $this_year = [0,0,0,0,0,0,0,0,0,0,0,0,0];
    foreach($this->entity->getRepository(FlProjectEntries::class)->getYearProfitByMonth($user) as $ym)
      {
      $this_year[(int)$ym['m']] = $ym['sum'];
      }
    return $this->render('freelancermanager/index.html.twig',[
      'NO_CONFIG'       => $noConfig,
      'CONFIG'          => $config,
      'CUSTOMER_STATS'  => $cust_stats,
      'PROJECT_COUNT'   => $prjcnt,
      'INVOICE_COUNT'   => $this->entity->getRepository(FlInvoices::class)->count(['RefUser' => $user]),
      'PRJ_ENTRY_COUNT' => $this->entity->getRepository(FlProjectEntries::class)->count(['RefUser' => $user]),
      'CONTRACT_COUNT'  => $this->entity->getRepository(FlServiceContracts::class)->count(['RefUser' => $user]),
      'LAST_ENTRIES'    => $this->entity->getRepository(FlProjectEntries::class)->getLastEntries($user,4),
      'LABELS'          => $labels,
      'VALUES'          => $values,
      'THIS_YEAR_MONTH' => $mons,
      'THIS_YEAR_SUM'   => $this_year,
      'CURRENT_YEAR'    => date('Y'),
      ]);
    }
  }
