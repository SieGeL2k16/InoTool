<?php declare(strict_types=1);
/**
 * All reporting methods
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (10-Apr-2023)
 */
namespace App\Controller;

use App\Entity\FlConfiguration;
use App\Service\ReportingHelper;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route("/reporting/")]
class ReportingController extends AbstractController
  {
  const ACTNAV = 'reports';
  /** @var EntityManagerInterface $entity */
  private EntityManagerInterface $entity;
  private ReportingHelper $reportingHelper;
  
  /**
   * @param EntityManagerInterface $entity
   * @param ReportingHelper $reportingHelper
   */
  public function __construct(EntityManagerInterface $entity, ReportingHelper $reportingHelper)
    {
    $this->entity = $entity;
    $this->reportingHelper = $reportingHelper;
    }
  
  /**
   * Renders overview page of reportings
   * @return Response
   * @throws Exception
   */
  #[Route("overview",name: "fl_reporting_index")]
  public function index():Response
    {
    $user = $this->getUser();
    $TOTALS = $this->reportingHelper->getTotals($user->getId());
    $TOTALS['AVG_HOURLY_RATE'] = round($TOTALS['total_salary'] / ($TOTALS['total_seconds'] / 3600),2);
    return $this->render('freelancermanager/reporting_index.html.twig',[
      'ACTNAV'    => self::ACTNAV,
      'TOP_TIME'  => $this->reportingHelper->getTopWorkingTime($user->getId()),
      'TOP_PAYED' => $this->reportingHelper->getTopPaidProjects($user->getId()),
      'CONFIG'    => $this->entity->getRepository(FlConfiguration::class)->findOneBy(['RefUser' => $user]),
      'OVERVIEW'  => $this->reportingHelper->getOverview($user->getId()),
      'TOTALS'    => $TOTALS,
      ]);
    }
  
  }
