<?php declare(strict_types=1);
/**
 * Account Data browser with edit functionality.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (01-Jan-2022)
 */

namespace App\Controller\KontoManager;

use App\Entity\AccountCategories;
use App\Entity\AccountData;
use App\Entity\User;
use App\Service\AppConfigHelper;
use DateTime;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use IntlDateFormatter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_USER')]
class OverviewController extends AbstractController
  {
  /**
   * @param Request $request
   * @param ManagerRegistry $registry
   * @param AppConfigHelper $appConfigHelper
   * @return Response
   * @throws Exception
   */
  #[Route('/kontomanager/liste',name: 'km_list')]
  public function index(Request $request, ManagerRegistry $registry, AppConfigHelper $appConfigHelper):Response
    {
    /** @var User $user */
    $user       = $this->getUser();
    $f_category = $request->get('f_cat');
    $f_month    = $request->get('f_month');
    $f_year     = $request->get('f_year');
    $f_search   = $request->get('f_search');
    if($f_category === null)
      {
      $f_category = $appConfigHelper->Get(User::CFG_ACC_BROWSER_F_CAT,"-1",$user);
      }
    else
      {
      $appConfigHelper->Set(User::CFG_ACC_BROWSER_F_CAT,(string)$f_category,$user);
      }
    if($f_month === null)
      {
      $f_month = $appConfigHelper->Get(User::CFG_ACC_BROWSER_F_MONTH,date('m'),$user);
      }
    else
      {
      $appConfigHelper->Set(User::CFG_ACC_BROWSER_F_MONTH,(string)$f_month,$user);
      }
    if($f_year === null)
      {
      $f_year = $appConfigHelper->Get(User::CFG_ACC_BROWSER_F_YEAR,date('Y'),$user);
      }
    else
      {
      $appConfigHelper->Set(User::CFG_ACC_BROWSER_F_YEAR,(string)$f_year,$user);
      }
    // @todo Make locale configurable / read from user's setting
    $dfmt = new IntlDateFormatter( "de-DE" ,IntlDateFormatter::FULL, IntlDateFormatter::NONE,null,null,"MMMM");
    $month_list = [];
    for($i = 1; $i < 13; $i++)
      {
      $dnow = new DateTime(sprintf("%04d-%02d-01",date("Y"),$i));
      $month_list[$i] = $dfmt->format($dnow);
      }
    $dbstats = $registry->getRepository(AccountData::class)->GetDatabaseStatistics($user);
    $Ymin = (new DateTime($dbstats['MIN_DATE']))->format('Y');
    $Ymax = date('Y');    // Generate year selection up to current year
    $year_list = [];
    for($i = (int)$Ymax; $i > (int)$Ymin-1; $i--)
      {
      $year_list[] = $i;
      }
    $data = $registry->getRepository(AccountData::class)->GetBrowserData($user,['F_CATEGORY' => $f_category,'F_MONTH' => $f_month,'F_YEAR' => $f_year,'F_SEARCH' => '']);
    $revenue = ['INCOME' => 0.00, 'OUTCOME' => 0.00,'DIFF' => 0.00];
    foreach($data as $item)
      {
      if($item['is_income'])
        {
        $key = 'INCOME';
        }
      else
        {
        $key = 'OUTCOME';
        }
      $revenue[$key]+=$item['amount'];
      }
    $revenue['DIFF'] = abs($revenue['INCOME']) - abs($revenue['OUTCOME']);
    return $this->render('kontomanager/list.html.twig',[
      'ACTNAV'        => 'list',
      'CATEGORYLIST'  => $registry->getRepository(AccountCategories::class)->getCategoryList($user),
      'MONTHLIST'     => $month_list,
      'YEARLIST'      => $year_list,
      'F_CATEGORY'    => $f_category,
      'F_MONTH'       => $f_month,
      'F_YEAR'        => $f_year,
      'DATA'          => $data,
      'DATA_COUNT'    => count($data),
      'TOTAL_COUNT'   => $dbstats['TOTAL_ROWS'],
      'REVENUE'       => $revenue,
      ]);
    }
  
  /**
   * Ajax backend for datatables
   * @param Request $request
   * @param ManagerRegistry $registry
   * @return JsonResponse
   * @throws Exception
   */
  #[Route('/kontomanager/liste-ajax',name: 'km_listAjax')]
  public function ajaxList(Request $request,ManagerRegistry $registry):JsonResponse
    {
    /** @var User $user */
    $user     = $this->getUser();
    // error_log(var_export($_REQUEST,true));
    $draw     = $request->get('draw');    // Draw counter for datatable rendering
    $start    = $request->get('start');   // 0-based starting index
    $length   = $request->get('length');  // Number of records to show
    $search   = $request->get('search');  // Global search term
    $order    = $request->get('order');   // Array holds ordering informations
    $sd       = $request->get('SD');
    $ed       = $request->get('ED');
    /* Prepare parameter to pass directly to Repository: */
    $sort_col = (int)$order[0]['column'] + 1;
    if($sort_col > 4)
      {
      $sort_col = 1;
      }
    $sort_dir = strtolower($order[0]['dir']);
    if($sort_dir !== "asc" && $sort_dir !== "desc")
      {
      $sort_dir = "asc";
      }
    // Workaround to render table data sorted by datetime desc instead of asc:
    if($draw == 1)
      {
      $sort_col = 2;
      $sort_dir = "desc";
      }
    $params = [
      'SD'      => $sd.' 00:00:00',
      'ED'      => $ed.' 23:59:59',
      'START'   => (int)$start,
      'LIMIT'   => (int)$length,
      'SEARCH'  => $search['value'],
      'ORDER'   => $sort_col,
      'SDIR'    => $sort_dir,
      ];
    $data     = $registry->getRepository(AccountData::class)->GetDataTablesValues($user->getId(),$params);
    $total    = $registry->getRepository(AccountData::class)->count(['RefUser' => $user]);
    //error_log("TOTAL=".$total."|DATA_TOTAL=".$data['TOTAL']);
    return new JsonResponse([
      'draw'            => (int)$draw,
      'recordsTotal'    => $total,
      'recordsFiltered' => $data['TOTAL'],
      'data'            => $data['DATA'],
      ]);
    }

  }
