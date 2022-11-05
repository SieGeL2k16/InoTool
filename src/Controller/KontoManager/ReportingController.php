<?php declare(strict_types=1);
/**
 * All reported related calls
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (16-Jul-2022)
 */

namespace App\Controller\KontoManager;

use App\Entity\AccountCategories;
use App\Entity\AccountData;
use App\Entity\User;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use IntlDateFormatter;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
   * @param int $year
   * @return Response
   */
  #[Route("/kontomanager/report/uebersicht/{year}",name: "km_report_overview")]
  public function overview(int $year = 0):Response
    {
    /** @var User $user */
    $user = $this->getUser();
    return $this->render('kontomanager/report_overview.html.twig', [
      'ACTNAV'    => self::ACT_NAV,
      'YEARLIST'  => $this->registry->getRepository(AccountData::class)->GetDistinctYears($user->getId()),
      'ACTYEAR'   => $year,
      ]);
    }
  
  /**
   * @param int $year
   * @return JsonResponse
   */
  #[Route("/kontomanager/report/ajax-overview/{year}",name: "km_report_ajaxOverview")]
  public function loadData(int $year = 0):JsonResponse
    {
    /** @var User $user */
    $user = $this->getUser();
    $ydata= $this->registry->getRepository(AccountData::class)->GetYearlyStats($user->getId(),$year);
    $ydata['INCOME']  = (float)$ydata['INCOME'];
    $ydata['OUTCOME'] = (float)$ydata['OUTCOME'];
    return new JsonResponse([
      'PIE_DATA'  => $ydata,
      'YEAR_DATA' => $this->registry->getRepository(AccountData::class)->GetInOutByYear($user->getId(),$year),
      ]);
    }
  
  /**
   * Lists overview page for costs / months / category
   * @param int $year
   * @return Response
   */
  #[Route("/kontomanager/report/kostenMonat/{year}",name: "km_report_costsMonth")]
  public function showCostsPerMonth(int $year = 0):Response
    {
    /** @var User $user */
    $user = $this->getUser();
    $ylist= $this->registry->getRepository(AccountData::class)->GetDistinctYears($user->getId());
    if($year === 0) {
      $year = (int)$ylist[0]['y'] ?? date('Y');
      }
    return $this->render('kontomanager/report_costs_month.html.twig', [
      'ACTNAV'    => self::ACT_NAV,
      'YEARLIST'  => $ylist,
      'ACTYEAR'   => $year,
      ]);
    }
  
  /**
   * Calculates all values, renders HTML table from template and returns it as JSON object.
   * @param Request $request
   * @param int $year
   * @return JsonResponse
   */
  #[Route("/kontomanager/report/kostenMonatAjax/{year}",name: "km_report_costsMonthAjax")]
  public function renderCostsPerMonth(Request $request,int $year = 0):JsonResponse
    {
    $tz  = ini_get("date.timezone");
    if(empty($tz) === true)
      {
      $tz = "Europe/Berlin";    // Fallback
      }
    $fmt = new IntlDateFormatter($request->getLocale(), IntlDateFormatter::FULL, IntlDateFormatter::FULL, $tz, IntlDateFormatter::GREGORIAN, 'MMM');
    /** @var User $user */
    $user = $this->getUser();
    $mons = [];
    for($i = 1; $i < 13; $i++)
      {
      $dt = new DateTime("01-$i-2022 00:00:00");
      $mons[$i] = $fmt->format($dt);
      }
    $data = $this->registry->getRepository(AccountData::class)->GetCostsPerMonth($user->getId(),$year);
    $html = $this->render('kontomanager/report_costs_month_table.html.twig', [
      'MONTHS'  => $mons,
      'DATA'    => $data,
      ])->getContent();
    return new JsonResponse(['HTML' => $html]);
    }
  
  /**
   * Returns entries for a given Category ID + Month + year as modal dialog
   * @param Request $request
   * @return JsonResponse
   */
  #[Route("/kontomanager/report/listeKatMonAjax",name: "km_report_listCatMonAjax",methods: ["POST"])]
  public function showDataForCategoryAndMonth(Request $request):JsonResponse
    {
    $catid= (int)$request->get('catid', 0);
    $mon  = (int)$request->get('month',0);
    $year = (int)$request->get('year',0);
    /** @var User $user */
    $user = $this->getUser();
    $data = $this->registry->getRepository(AccountData::class)->GetListByCatMonYear($user->getId(),$catid,$mon,$year);
    return new JsonResponse([
      'HTML' => $this->render('kontomanager/report_costs_month_modal.html.twig',[
      'DATA'    => $data,
      'CATLIST' => $this->registry->getRepository(AccountCategories::class)->getCategoryList($user),
      ])->getContent(),
    ]);
    }
  
  /**
   * Saves changes from modal dialog
   * @param Request $request
   * @return Response
   */
  #[Route("/kontomanager/report/speichernKatMon",name: "km_report_listcat_save", methods: ["POST"])]
  public function SaveFromCategoryAndMonth(Request $request):Response
    {
    $year = null;
    foreach($request->get('cat') as $item)
      {
      $ids = explode("|",$item);    // 0 => AccId, 1 => CatId
      $obj = $this->registry->getRepository(AccountData::class)->find((int)$ids[0]);
      if($obj === null)
        {
        $this->logger->warning(__METHOD__.": Object with ID={$ids[0]} not found?!");
        continue;
        }
      $year = $obj->getBookingDate()->format('Y');
      // No good idea here, unnecessary query for categories. @todo Refactor!
      $cat = $this->registry->getRepository(AccountCategories::class)->find((int)$ids[1]);
      $obj->setRefCategory($cat);
      $this->registry->getManager()->persist($obj);
      if($cat !== null)
        {
        $this->logger->info("Set cat {$cat->getName()} on account data entry #{$obj->getId()}");
        }
      else
        {
        $this->logger->info("Set cat to NULL on account data entry #{$obj->getId()}");
        }
      }
    $this->registry->getManager()->flush();
    $this->addFlash("success","Daten wurden erfolgreich aktualisiert!");
    return $this->redirectToRoute("km_report_costsMonth",['year' => $year]);
    }
  }
