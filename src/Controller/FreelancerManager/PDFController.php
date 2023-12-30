<?php declare(strict_types=1);
/**
 * PDF related code.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (29-Dec-2023)
 */

namespace App\Controller\FreelancerManager;

use App\Entity\FlConfiguration;
use App\Repository\FlConfigurationRepository;
use App\Repository\FlProjectEntriesRepository;
use App\Service\ReportingHelper;
use App\Service\tcpdf_helper;
use DateTime;
use Doctrine\DBAL\Exception;
use IntlDateFormatter;
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
  public function __construct(private readonly ReportingHelper $reportingHelper,
                              private readonly tcpdf_helper $tcpdf_helper,
                              private readonly FlConfigurationRepository $configurationRepository){}
  
  /**
   * Renders PDF output for a given Project covering a given date range
   * @param Request $request
   * @param LoggerInterface $logger
   * @param FlProjectEntriesRepository $entriesRepository
   * @return Response
   * @throws Exception
   */
  #[Route("by-project", name: "pdf_report_by_project")]
  public function byproject(Request $request,LoggerInterface $logger,
                            FlProjectEntriesRepository $entriesRepository):Response
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
      /** @var FlConfiguration $company */
    $company = $this->configurationRepository->findOneBy(['RefUser' => $user->getId()]);
    if($company === null)
      {
      $this->addFlash('warning',"PDF Erzeugung nicht möglich - Keine Firmendaten gefunden?!");
      $logger->warning(__METHOD__.": company object is empty?");
      return $this->redirectToRoute("fl_index");
      }
    $report = $entriesRepository->getEntriesForProjectAndRange($user,(int)$pid,new DateTime($sd),new DateTime($ed));
    $buffer = $this->render("freelancermanager/pdf/reporting_by_project.html.twig", [
      'ENTRIES'       => $report['data'],
      'PROJECT_NAME'  => $report['totals']['PROJECT'],
      'TOTAL_SECONDS' => $report['totals']['WORK_TIME'],
      'TOTAL_SALARY'  => $report['totals']['SALARY'],
      ])->getContent();
    $df = IntlDateFormatter::create($request->getLocale(),IntlDateFormatter::MEDIUM,IntlDateFormatter::NONE);
    $dt_sd = new DateTime($sd);
    $dt_ed = new DateTime($ed);
    $trange = $this->reportingHelper->getDateRangeText($df->format($dt_sd),$df->format($dt_ed));
    $TITLE  = sprintf("Auswertung für %s",$report['totals']['CUSTOMER']);
    $this->tcpdf_helper->Init('P','mm','A4',true);
    $pdf = $this->tcpdf_helper->SetMetaData($user);
    $pdf->setTitle($TITLE." (Zeitraum: $trange)");
    $pdf->setSubject("Stundenauswertung");
    $pdf->setLeftMargin(tcpdf_helper::PAGE_MARGIN_X);
    $pdf->setRightMargin(tcpdf_helper::PAGE_MARGIN_X);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetColor('text',0,0,0);
    $pdf->Cell(0,16,$TITLE,0,true,'L',0,'',0,false,'B','B');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetY(16);
    $pdf->Cell(0,16,'Stundenauflistung von '.$company->getCompanyName(),0,true,'L',0,'',0,false,'B','B');
    $pdf->SetY(21);
    $pdf->Cell(0,16,'Zeitraum: '.$trange,0,false,'L',0,'',0,false,'B','B');
    $pdf->WriteHTMLCell(0,0,tcpdf_helper::PAGE_MARGIN_X,30,$buffer);
    $pdfbuffer = $pdf->Output("ignored.pdf","S");
    $filename  = sprintf("%s_%s-%s_stundenauswertung.pdf",str_replace(" ","_",mb_strtolower($report['totals']['PROJECT'])),$dt_sd->format("Ymd"),$dt_ed->format("Ymd"));
    return $this->tcpdf_helper->Download($filename,$pdfbuffer);
    }
  }
/*
 * array:2 [▼
  "data" => array:4 [▼
    0 => array:8 [▼
      "id" => 10905
      "salary" => "45.00"
      "ymd" => "2023-12-29"
      "project_name" => "IT Administration"
      "customer_name" => "Rechtsanwalt Frank Meier"
      "no_reporting" => false
      "work_time_in_secs" => 1800
      "work_description" => "Prüfung Backup, Einrichtung Strato HiDrive, Einbindung HiDrive in Synology"
    ]
    1 => array:8 [▶]
    2 => array:8 [▶]
    3 => array:8 [▶]
  ]
  "totals" => array:6 [▼
    "ROWS" => 4
    "WORK_TIME" => 12600
    "SALARY" => 315.0
    "PROJECT" => "IT Administration"
    "CUSTOMER" => "Rechtsanwalt Frank Meier"
    "NO_REPORTING" => false
  ]
]
 */
