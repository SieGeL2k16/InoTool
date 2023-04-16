<?php declare(strict_types=1);
/**
 * All reporting methods
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (10-Apr-2023)
 */
namespace App\Controller\FreelancerManager;

use App\Entity\FlConfiguration;
use App\Entity\FlProjectEntries;
use App\Service\globalHelper;
use App\Service\ReportingHelper;
use DateTime;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use IntlDateFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route("/freelancer/reporting/")]
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
   * @param Request $request
   * @return Response
   * @throws Exception
   */
  #[Route("overview",name: "fl_report_index")]
  public function index(Request $request):Response
    {
    $dt = new DateTime();
    $arr = [];
    $fmt = new IntlDateFormatter($request->getLocale(), IntlDateFormatter::FULL, IntlDateFormatter::FULL, globalHelper::getTZ(), IntlDateFormatter::GREGORIAN, 'MMMM YYYY');
    $user = $this->getUser();
    $TOTALS = $this->reportingHelper->getTotals($user->getId());
    $TOTALS['AVG_HOURLY_RATE'] = round($TOTALS['total_salary'] / ($TOTALS['total_seconds'] / 3600),2);
    $total_sal = $mon_sal = $mon_virt_sal = 0.00 ;
    $total_time = $mon_time = 0;
    foreach($this->entity->getRepository(FlProjectEntries::class)->getEntriesFromYYYYMM($user,$dt->format('Ym')) as $item)
      {
      $mon_time+=$item['work_time_in_secs'];
      if($item['no_reporting'] === false)
        {
        $mon_sal+=$item['salary'];
        }
      else
        {
        $mon_virt_sal+=$item['salary'];
        }
      }
    foreach($this->entity->getRepository(FlProjectEntries::class)->getYearlyProfit($user) as $d)
      {
      if((int)$d['y'] === (int)date('Y'))
        {
        $total_sal = $d['sum'];
        $total_time= $d['sum_worktime'];
        break;
        }
      }
    return $this->render('freelancermanager/reporting_index.html.twig',[
      'ACTNAV'          => self::ACTNAV,
      'TOP_TIME'        => $this->reportingHelper->getTopWorkingTime($user->getId()),
      'TOP_PAYED'       => $this->reportingHelper->getTopPaidProjects($user->getId()),
      'CONFIG'          => $this->entity->getRepository(FlConfiguration::class)->findOneBy(['RefUser' => $user]),
      'OVERVIEW'        => $this->reportingHelper->getOverview($user->getId()),
      'TOTALS'          => $TOTALS,
      'WORKTIME_MONTH'  => $mon_time,
      'SAL_MONTH'       => $mon_sal,
      'SAL_VIRT_MONTH'  => $mon_virt_sal,
      'CURRENT_MONTH'   => $fmt->format($dt),
      'WORKTIME_YEAR'   => $total_time,
      'SAL_YEAR'        => $total_sal,
      ]);
    }
  
  /**
   * Returns all project entries for current user and given year+month combo
   * @param Request $request
   * @return JsonResponse
   */
  #[Route("ajax/entries-yyyymm",name: "fl_report_ajax_entries_yyyymm",methods: ["POST"])]
  public function showEntriesForYYYYMM(Request $request):JsonResponse
    {
    $yyyymm = $request->get('YM');
    $user = $this->getUser();
    $data = $this->entity->getRepository(FlProjectEntries::class)->getEntriesFromYYYYMM($user,$yyyymm);
    // Calculate total worktime + salary
    $total_time   = 0;
    $total_salary = 0.0;
    $virtual_salary = 0.0;
    foreach($data as $d)
      {
      if((bool)$d['no_reporting'] === false)
        {
        $total_salary+=$d['salary'];
        }
      else
        {
        $virtual_salary+=$d['salary'];
        }
      $total_time+=$d['work_time_in_secs'];
      }
    return new JsonResponse([
      'HTML' => $this->render('freelancermanager/_entries_yyyymm.html.twig', ['DATA' => $data,'TOTAL_TIME' => $total_time,'TOTAL_SALARY' => $total_salary])->getContent(),
    ]);
  }
  
}
