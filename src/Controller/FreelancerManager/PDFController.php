<?php declare(strict_types=1);
/**
 * PDF related code.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (29-Dec-2023)
 */

namespace App\Controller\FreelancerManager;

use App\Repository\FlProjectEntriesRepository;
use DateTime;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/pdf/")]
#[IsGranted("ROLE_USER")]
class PDFController extends AbstractController
  {
  #[Route("by-project", name: "pdf_report_by_project")]
  public function byproject(Request $request,LoggerInterface $logger,
                            FlProjectEntriesRepository $entriesRepository)
    {
    $pid  = $request->get('pid');
    $sd   = $request->get('sd');
    $ed   = $request->get('ed');
    $user = $this->getUser();
    if($pid === null || $sd === null || $ed === null)
      {
      $this->addFlash('warning',"PDF Erzeugung nicht möglich - Daten fehlerhaft!");
      $logger->warning(__METHOD__.": PID=$pid|SD=$sd|ED=$ed");
      return $this->redirectToRoute("fl_index");
      }
    $report = $entriesRepository->getEntriesForProjectAndRange($user,(int)$pid,new DateTime($sd),new DateTime($ed));
    $buffer = $this->render("freelancermanager/pdf/reporting_by_project.html.twig", [
      'ENTRIES' => $report['data'],
      'TOTALS'  => $report['totals'],
      
      ])->getContent();
    dd("STOP",$report,$buffer);
    }
  }
