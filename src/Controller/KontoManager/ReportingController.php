<?php declare(strict_types=1);
/**
 * All reported related calls
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (16-Jul-2022)
 */

namespace App\Controller\KontoManager;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_USER')]
class ReportingController extends AbstractController
  {
  const ACT_NAV = 'report';

  /** @var ManagerRegistry $registry */
  private ManagerRegistry $registry;

  /** @var LoggerInterface $logger */
  private LoggerInterface $logger;
  
  public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
    $this->registry = $registry;
    $this->logger = $logger;
    }
  
  /**
   * Displays overview with income/outcome graphs
   * @return Response
   */
  #[Route("/kontomanager/report/uebersicht",name: "km_report_overview")]
  public function overview():Response
    {
    return $this->render('kontomanager/report_overview.html.twig', [
      'ACTNAV'    => self::ACT_NAV,
      ]);
    }
  
  }
