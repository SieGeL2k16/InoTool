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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
   * @return Response
   */
  #[Route('/freelancer',name: 'fl_index')]
  public function index():Response
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
    $prjcnt = $this->entity->getRepository(FlProjects::class)->count(['RefUser' => $user]);
    return $this->render('freelancermanager/index.html.twig',[
      'NO_CONFIG'       => $noConfig,
      'CONFIG'          => $config,
      'CUSTOMER_STATS'  => $cust_stats,
      'PROJECT_COUNT'   => $prjcnt,
      'INVOICE_COUNT'   => $this->entity->getRepository(FlInvoices::class)->count(['RefUser' => $user]),
      'PRJ_ENTRY_COUNT' => $this->entity->getRepository(FlProjectEntries::class)->count(['RefUser' => $user]),
      ]);
    }
  }
