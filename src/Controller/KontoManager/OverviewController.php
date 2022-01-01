<?php declare(strict_types=1);
/**
 * Account Data browser with edit functionality.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (01-Jan-2022)
 */

namespace App\Controller\KontoManager;

use App\Entity\AccountData;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
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
   * @return Response
   */
  #[Route('/kontomanager/liste',name: 'km_list')]
  public function index():Response
    {
    return $this->render('kontomanager/list.html.twig',[
      'ACTNAV' => 'list',
    ]);
    }
  
  /**
   * Ajax backend for datatables
   * @param Request $request
   * @param ManagerRegistry $registry
   * @return JsonResponse
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
    /** If no data is given defaults to current month: */
/*
      if($sd === null)
      {
        $sd = (new DateTime("now"))->format('Y-m-01');
      }
      else
      {
        $sd = (new DateTime($sd))->format('Y-m-d');
      }
      if($ed === null)
      {
        $ed = (new DateTime("now"))->format('Y-m-t');
      }
      else
      {
        $ed = (new DateTime($ed))->format('Y-m-d');
      }
*/
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
    error_log("TOTAL=".$total."|DATA_TOTAL=".$data['TOTAL']);
    return new JsonResponse([
      'draw'            => (int)$draw,
      'recordsTotal'    => $total,
      'recordsFiltered' => $data['TOTAL'],
      'data'            => $data['DATA'],
      ]);
    }

  }
