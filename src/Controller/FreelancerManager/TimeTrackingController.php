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

  /** @var timeTrackingHelper $timeTrackingHelper */
  private timeTrackingHelper $timeTrackingHelper;
  
  public function __construct(timeTrackingHelper $timeTrackingHelper)
    {
    $this->timeTrackingHelper = $timeTrackingHelper;
    }
  
  /**
   * @return Response
   * @throws Exception
   */
  #[Route("form",name: "fl_time_form")]
  public function form():Response
    {
    $dt = new DateTime(date("Ym")."01");
    $dt->sub(new DateInterval("P1M"));
    return $this->render('freelancermanager/timetracking_form.html.twig',[
      'ACTNAV'    => self::ACTNAV,
      'CALENDAR'  => $this->timeTrackingHelper->createCalendar((int)$dt->format("m"),(int)$dt->format("Y"),3),
      ]);
      
    }
  }
