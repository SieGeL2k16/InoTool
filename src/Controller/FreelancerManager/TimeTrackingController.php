<?php declare(strict_types=1);
/**
 * Time tracking controller.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (01-Jan-2023)
 */

namespace App\Controller\FreelancerManager;

use App\Service\timeTrackingHelper;
use DateInterval;
use DateTime;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 *
 */
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
   * @param string|null $date
   * @return Response
   * @throws Exception
   */
  #[Route("form/{date}",name: "fl_time_form")]
  public function form(string $date = null):Response
    {
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
    return $this->render('freelancermanager/timetracking_form.html.twig',[
      'ACTNAV'        => self::ACTNAV,
      'CURRENT_DATE'  => $dt,
      'CALENDAR'      => $this->timeTrackingHelper->createCalendar((int)$st->format("m"),(int)$st->format("Y"),self::CAL_CNT),
      ]);
      
    }
  }
