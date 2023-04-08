<?php declare(strict_types=1);
/**
 * Time tracking controller.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (01-Jan-2023)
 */

namespace App\Controller\FreelancerManager;

use App\Entity\FlProjectEntries;
use App\Entity\FlProjects;
use App\Repository\FlProjectEntriesRepository;
use App\Repository\FlProjectsRepository;
use App\Service\timeTrackingHelper;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_USER")]
#[Route("/freelancer/timetracking/")]
class TimeTrackingController extends AbstractController
  {
  const ACTNAV = 'time';

  const CAL_CNT = 3;    // How many calendars should be shown - defaults to 3
  
  /** @var timeTrackingHelper $timeTrackingHelper */
  private timeTrackingHelper $timeTrackingHelper;
  
  /** @var LoggerInterface $logger */
  private LoggerInterface $logger;
  
  /**
   * Constructor
   * @param timeTrackingHelper $timeTrackingHelper
   * @param LoggerInterface $logger
   */
  public function __construct(timeTrackingHelper $timeTrackingHelper,LoggerInterface $logger)
    {
    $this->timeTrackingHelper = $timeTrackingHelper;
    $this->logger = $logger;
    }
  
  /**
   * Renders Time tracking form for a given date.
   * @param FlProjectsRepository $projectsRepository
   * @param FlProjectEntriesRepository $entriesRepository
   * @param string|null $date
   * @return Response
   * @throws Exception
   */
  #[Route("form/{date}",name: "fl_time_form")]
  public function form(FlProjectsRepository $projectsRepository, FlProjectEntriesRepository $entriesRepository,string $date = null):Response
    {
    $user= $this->getUser();
    if($date === null)
      {
      $dt = new DateTime();
      }
    else
      {
      try
        {
        $dt = new DateTime($date);
        }
      catch(Exception $e)
        {
        $this->logger->warning(__METHOD__.": Invalid date: ".$e->getMessage());
        $this->addFlash('warning',"Ungültiges Datum!");
        $dt = new DateTime();
        }
      }
    $dates = $this->getCalendarRange($dt);
    $tentry = new FlProjectEntries();
    return $this->render('freelancermanager/timetracking_form.html.twig',[
      'ACTNAV'        => self::ACTNAV,
      'CURRENT_DATE'  => $dt,
      'CALENDAR'      => $this->timeTrackingHelper->createCalendar((int)$dates['ST']->format("m"),(int)$dates['ST']->format("Y"),self::CAL_CNT),
      'EVENTS'        => $this->timeTrackingHelper->GetEventsForDateRange($user,$dates['ST'],$dates['ET']),
      'PROJECTS_LIST' => $projectsRepository->findBy(['RefUser' => $user,'Status' => FlProjects::PROJ_STATUS_ACTIVE],['ProjectName' => 'asc']),
      'TODAY_ENTRIES' => $entriesRepository->getEntriesForDate($user,$dt->format('Y-m-d')),
      'ENTRY'         => $tentry,
      'START_TIME'    => '00:00',
      'END_TIME'      => '00:00',
      'DURATION_HH'   => '',
      'DURATION_MM'   => '',
      ]);
    }
  
  /**
   * Calculates start- and endtime fpr a given DateTime object.
   * Uses self::CAL_CNT as base.
   * @param DateTime $dt
   * @return DateTime[]
   * @throws Exception
   */
  private function getCalendarRange(DateTime $dt):array
    {
    $ym = $dt->format('Ym');
    $st = new DateTime("{$ym}01 00:00:00");
    $st->sub(new DateInterval("P1M"));
    $et = clone $st;
    $et->add(new DateInterval("P".(self::CAL_CNT-1)."M"));  // Startedate is already set!
    return ['ST' => $st, 'ET' => $et];
    }
  
  /**
   * Edits an entry.
   * @param int $id
   * @param FlProjectEntriesRepository $entriesRepository
   * @param FlProjectsRepository $projectsRepository
   * @return Response
   * @throws \Doctrine\DBAL\Exception
   */
  #[Route("edit/{id}",name: "fl_time_edit")]
  public function edit(int $id,FlProjectEntriesRepository $entriesRepository,FlProjectsRepository $projectsRepository):Response
    {
    $user   = $this->getUser();
    $entry  = $entriesRepository->find($id);
    if($entry === null)
      {
      $this->logger->warning(__METHOD__.": No data found with ID=$id - editing not possible");
      $this->addFlash('warning',"Editieren nicht möglich - keinen Eintrag gefunden?!");
      return $this->redirectToRoute("fl_time_form");
      }
    /** @var DateTime $edate */
    $edate  = $entry->getEntryDate();
    $dates  = $this->getCalendarRange($edate);
    $wtime  = $this->timeTrackingHelper->getWorkTimeFromSeconds($entry->getWorkTimeInSecs());
    $enddt  = clone $edate;
    $enddt->add(new DateInterval("PT".$entry->getWorkTimeInSecs()."S"));
    return $this->render('freelancermanager/timetracking_form.html.twig',[
      'ACTNAV'        => self::ACTNAV,
      'CURRENT_DATE'  => $entry->getEntryDate(),
      'CALENDAR'      => $this->timeTrackingHelper->createCalendar((int)$dates['ST']->format("m"),(int)$dates['ST']->format("Y"),self::CAL_CNT),
      'EVENTS'        => $this->timeTrackingHelper->GetEventsForDateRange($user,$dates['ST'],$dates['ET']),
      'PROJECTS_LIST' => $projectsRepository->findBy(['RefUser' => $user,'Status' => FlProjects::PROJ_STATUS_ACTIVE],['ProjectName' => 'asc']),
      'TODAY_ENTRIES' => $entriesRepository->getEntriesForDate($user,$entry->getEntryDate()->format('Y-m-d')),
      'ENTRY'         => $entry,
      'START_TIME'    => sprintf("%s:%s",$edate->format('H'),$edate->format('i')),
      'END_TIME'      => sprintf("%s:%s",$enddt->format('H'),$enddt->format('i')),
      'DURATION_HH'   => $wtime['H'],
      'DURATION_MM'   => $wtime['M'],
      ]);
    }
  
  /**
   * Saves form
   * @param Request $request
   * @param FlProjectEntriesRepository $entriesRepository
   * @return Response
   */
  #[Route("save", name: "fl_time_save",methods: ["POST"])]
  public function save(Request $request,FlProjectEntriesRepository $entriesRepository):Response
    {
    $user= $this->getUser();
    $tid = $request->get('entryid');
    if($tid === null)
      {
      $fle = new FlProjectEntries();
      $fle->setCreatedOn(new DateTimeImmutable())->setRefUser($user);
      }
    else
      {
      $fle = $entriesRepository->find((int)$tid);
      if($fle === null)
        {
        $this->logger->warning(__METHOD__.": No projectEntry found with ID={$tid} - creating new one");
        $fle = new FlProjectEntries();
        $fle->setCreatedOn(new DateTimeImmutable())->setRefUser($user);
        }
      else
        {
        $fle->setLastModifiedOn(new DateTime());
        }
      }

    dd($fle);
    }
  
  }
