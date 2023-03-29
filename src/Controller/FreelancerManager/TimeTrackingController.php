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
use App\Repository\FlCustomerRepository;
use App\Repository\FlProjectEntriesRepository;
use App\Repository\FlProjectsRepository;
use App\Service\timeTrackingHelper;
use DateInterval;
use DateTime;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    $ym = $dt->format('Ym');
    $st = new DateTime("{$ym}01 00:00:00");
    $st->sub(new DateInterval("P1M"));
    $et = clone $st;
    $et->add(new DateInterval("P".(self::CAL_CNT-1)."M"));  // Startedate is already set!
    $tentry = new FlProjectEntries();
    return $this->render('freelancermanager/timetracking_form.html.twig',[
      'ACTNAV'        => self::ACTNAV,
      'CURRENT_DATE'  => $dt,
      'CALENDAR'      => $this->timeTrackingHelper->createCalendar((int)$st->format("m"),(int)$st->format("Y"),self::CAL_CNT),
      'EVENTS'        => $this->timeTrackingHelper->GetEventsForDateRange($user,$st,$et),
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
   * Edits an entry.
   * @param int $id
   * @param FlProjectEntriesRepository $entriesRepository
   * @return Response
   */
  #[Route("edit/{id}",name: "fl_time_edit")]
  public function edit(int $id,FlProjectEntriesRepository $entriesRepository):Response
    {
    $entry = $entriesRepository->find($id);
    }
  
  }
