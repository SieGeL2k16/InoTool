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
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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
    $todays_entries = $entriesRepository->getEntriesForDate($user,$dt->format('Y-m-d'));
    $todays_time = 0;
    $todays_sum  = 0.00;
    foreach($todays_entries as $te)
      {
      $todays_time+=(int)$te['work_time_in_secs'];
      $todays_sum+=(float)$te['salary'];
      }
    return $this->render('freelancermanager/timetracking_form.html.twig',[
      'ACTNAV'        => self::ACTNAV,
      'CURRENT_DATE'  => $dt,
      'CALENDAR'      => $this->timeTrackingHelper->createCalendar((int)$dates['ST']->format("m"),(int)$dates['ST']->format("Y"),self::CAL_CNT),
      'EVENTS'        => $this->timeTrackingHelper->GetEventsForDateRange($user,$dates['ST'],$dates['ET']),
      'PROJECTS_LIST' => $projectsRepository->findBy(['RefUser' => $user,'Status' => FlProjects::PROJ_STATUS_ACTIVE],['ProjectName' => 'asc']),
      'TODAY_ENTRIES' => $todays_entries,
      'TODAY_TIME'    => $todays_time,
      'TODAY_SUM'     => $todays_sum,
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
    $todays_entries = $entriesRepository->getEntriesForDate($user,$entry->getEntryDate()->format('Y-m-d'));
    $todays_time = 0;
    foreach($todays_entries as $te)
      {
      $todays_time+=(int)$te['work_time_in_secs'];
      }
    return $this->render('freelancermanager/timetracking_form.html.twig',[
      'ACTNAV'        => self::ACTNAV,
      'CURRENT_DATE'  => $entry->getEntryDate(),
      'CALENDAR'      => $this->timeTrackingHelper->createCalendar((int)$dates['ST']->format("m"),(int)$dates['ST']->format("Y"),self::CAL_CNT),
      'EVENTS'        => $this->timeTrackingHelper->GetEventsForDateRange($user,$dates['ST'],$dates['ET']),
      'PROJECTS_LIST' => $projectsRepository->findBy(['RefUser' => $user,'Status' => FlProjects::PROJ_STATUS_ACTIVE],['ProjectName' => 'asc']),
      'TODAY_ENTRIES' => $todays_entries,
      'TODAY_TIME'    => $todays_time,
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
   * @param FlProjectsRepository $projectsRepository
   * @param EntityManagerInterface $em
   * @return Response
   */
  #[Route("save", name: "fl_time_save",methods: ["POST"])]
  public function save(Request $request,FlProjectEntriesRepository $entriesRepository,FlProjectsRepository $projectsRepository,EntityManagerInterface $em):Response
    {
    $user= $this->getUser();
    $tid = (int)$request->get('entryid',0);
    if($tid === 0)
      {
      $fle = new FlProjectEntries();
      $fle->setCreatedOn(new DateTimeImmutable())->setRefUser($user);
      $lmsg = "Adding new project entry";
      $imsg = "Neuen Eintrag hinzugefügt";
      }
    else
      {
      $fle = $entriesRepository->findOneBy(['id' => $tid, 'RefUser' => $user]);
      if($fle === null)
        {
        $this->logger->warning(__METHOD__.": No projectEntry found with ID=$tid - creating new one");
        $lmsg = "Force creation of new entry (TID=$tid)";
        $imsg = "Neuen Eintrag erzwungen, da bestehender nicht gefunden wurde?";
        $tid = null;
        $fle = new FlProjectEntries();
        $fle->setCreatedOn(new DateTimeImmutable())->setRefUser($user);
        }
      else
        {
        $fle->setLastModifiedOn(new DateTime());
        $lmsg = "Updating project entry #$tid";
        $imsg = "Eintrag #$tid aktualisiert";
        }
      }
    $st = $request->get('ST');
    $hh = $request->get('HH');
    $mm = $request->get('MM');
    $cd = $request->get('cdate');
    try
      {
      if($st !== null)
        {
        $dt = new DateTime($cd."T".$st);
        }
      else
        {
        $dt = new DateTime($cd);
        }
      $prj = $projectsRepository->findOneBy(['id' => $request->get('RefProjectId'),'RefUser' => $user]);
      if($prj === null)
        {
        throw new Exception("Invalid Project ID!");
        }
      $worktime = intval($hh) * 3600 + intval($mm) * 60;
      $fle->setEntryDate($dt)->setRefProject($prj)->setWorkDescription($request->get('wdesc'))->setWorkTimeInSecs($worktime)->setStatus(timeTrackingHelper::ENTRY_STATUS_ACTIVE);
      $fle->setRecordType(FlProjectEntries::RT_PROJECT_ENTRY);
      $em->persist($fle);
      $em->flush();
      $this->logger->info($lmsg);
      $this->addFlash('success',$imsg);
      return $this->redirectToRoute('fl_time_form',['date' => $cd]);
      }
    catch(Exception $e)
      {
      $this->logger->warning(__METHOD__.": Unable to validate data: ".$e->getMessage());
      $this->addFlash('warning',"Es ist ein Problem mit der Verarbeitung der Daten aufgetreten: ".$e->getMessage());
      if($tid === null)
        {
        return $this->redirectToRoute("fl_time_form",['date' => $cd]);
        }
      else
        {
        return $this->redirectToRoute('fl_time_edit',['id' => $tid]);
        }
      }
    }
  
  /**
   * Removes an entry from database.
   * @param Request $request
   * @param FlProjectEntriesRepository $entriesRepository
   * @param EntityManagerInterface $em
   * @return Response
   */
  #[Route("delete",name: "fl_time_delete", methods: ["POST"])]
  public function delete(Request $request, FlProjectEntriesRepository $entriesRepository,
                         EntityManagerInterface $em):Response
    {
    $id = (int)$request->get('ENTRYID',0);
    $user = $this->getUser();
    $entry = $entriesRepository->findOneBy(['id' => $id,'RefUser' => $user]);
    if($entry === null)
      {
      $this->logger->warning(__METHOD__.": No project_entry found with ID=$id - delete not possible!");
      $this->addFlash('warning',"Löschen nicht möglich - Datensatz nicht vorhanden!");
      return $this->redirectToRoute("fl_time_form");
      }
    $edate = $entry->getEntryDate()->format('Y-m-d');
    $em->remove($entry);
    $em->flush();
    $this->logger->info("Successfully removed project entry #$id");
    $this->addFlash('success',"Datensatz wurde gelöscht.");
    return $this->redirectToRoute("fl_time_form",['date' => $edate]);
    }
  
  }
