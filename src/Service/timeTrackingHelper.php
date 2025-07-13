<?php declare(strict_types=1);
/**
 * Helper methods for Time tracking module.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (01-Jan-2023)
 */

namespace App\Service;

use App\Entity\FlProjectEntries;
use App\Repository\FlProjectEntriesRepository;
use DateTime;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class timeTrackingHelper
  {
  /** @var int Statusflags for FlProjectEntries.Status field */
  const ENTRY_STATUS_ACTIVE   = 0;
  const ENTRY_STATUS_INACTIVE = 1;
  const ENTRY_STATUS_ARCHIVED = 2;

  /**
   * Returns array with date representation of $month / $year, with optional $cnt month following
   * @param int $month
   * @param int $year
   * @param int $cnt
   * @return array
   */
  public function createCalendar(int $month, int $year, int $cnt = 1):array
    {
    $cal = [];
    $m = $month;
    $y = $year;
    for($i = 0; $i < $cnt; $i++)
      {
      $cal[$y][$m] = $this->createCalArray($m,$y);
      $m++;
      if($m > 12)
        {
        $y++;
        $m = 1;
        }
      }
    return $cal;
    }

  /**
   * Creates an array with a complete month defined by passed parameter
   * @param int $month
   * @param int $year
   * @return array
   */
  public function createCalArray(int $month, int $year):array
    {
    $cal = [];
    $maxdays = (int)date("t",mktime(0,0,0,$month,1,$year));
    // Wo Mo Di Mi Do Fr Sa So
    $week_line = [];
    for($d = 1; $d < $maxdays + 1; $d++)
      {
      $ts = mktime(2,0,0,$month,$d,$year);
      $dstart = (int)date('N',$ts);
      if(empty($week_line) === true)
        {
        $week_line[0] = date('W',$ts);
        $week_line = array_merge($week_line,array_fill(1,7,null));
        }
      $week_line[$dstart] = $d;
      if($dstart === 7)
        {
        $cal[] = $week_line;
        $week_line = [];
        }
      }
    if(empty($week_line) === false)
      {
      $cal[] = $week_line;
      }
    return $cal;
    }

  /**
   * Checks for entries for given daterange and returns assoc. array with dates and number of events per date
   * @param UserInterface $user
   * @param DateTime $st
   * @param DateTime $et
   * @param FlProjectEntriesRepository $flProjectEntriesRepository
   * @return array
   * @throws Exception
   */
  public function GetEventsForDateRange(UserInterface $user,DateTime $st, DateTime $et,FlProjectEntriesRepository $flProjectEntriesRepository):array
    {
    $arr = [];
    foreach($flProjectEntriesRepository->getEventListByDateRange($user,$st,$et) as $item)
      {
      $arr[$item['edate']] = $item['cnt'];
      }
    return $arr;
    }

  /**
   * Returns given seconds in HH:MM format.
   * @param $seconds int The number of seconds to convert
   * @return string The formatted time in format HH:MM
   */
  public function formatWorkTime(int $seconds):string
    {
    if(($seconds / 86400) > 1)
      {
      $mydays = intval($seconds / 86400);
      $seconds-= $mydays * 86400;
      return(sprintf("%d:%02.2d:%02.2d",$mydays,($seconds / 3600),(($seconds / 60) >= 60) ? ($seconds - ((floor($seconds / 3600) * 3600))) / 60 : ($seconds / 60)));
      }
    else
      {
      return(sprintf("%02.2d:%02.2d",($seconds / 3600),(($seconds / 60) >= 60) ? ($seconds - ((floor($seconds / 3600) * 3600))) / 60 : ($seconds / 60)));
      }
    }

  /**
   * Returns the hours and minutes from given seconds as array.
   * @param $seconds int The number of seconds
   * @return array Associative array with keys "H" (for hours) and "M" (for minutes)
   */
  public function getWorkTimeFromSeconds(int $seconds): array
    {
    return [
      'H' => floor($seconds / 3600),
      'M' => (($seconds / 60) >= 60) ? ($seconds - ((floor($seconds / 3600) * 3600))) / 60 : ($seconds / 60)
      ];
    }

  /**
   * Returns seconds as localized string in format xx days, yyH etc.
   * @param int $seconds
   * @param string $locale
   * @return string
   * @todo This is 1:1 copy of the old code - should be refactorized...
   */
  public function formatWorkTimeLoc(int $seconds,string $locale = 'de'):string
    {
    if(($seconds / 86400) > 1)
      {
      if($locale == 'de')
        {
        $day  = 'Tag';
        $days = 'Tage';
        }
      else
        {
        $day  = 'Day';
        $days = 'Days';
        }
      $mydays = intval($seconds / 86400);
      $seconds-= $mydays * 86400;
      if($mydays == 1)
        {
        $md = $day;
        }
      else
        {
        $md = $days;
        }
      return(sprintf("%d %s, %02.2dh %02.2dmin %02.2ds",$mydays,$md,($seconds / 3600),(($seconds / 60) >= 60) ? ($seconds - ((floor($seconds / 3600) * 3600))) / 60 : ($seconds / 60),($seconds % 60)));
      }
    else
      {
      return(sprintf("%02.2dh %02.2dmin %02.2ds",($seconds / 3600),(($seconds / 60) >= 60) ? ($seconds - ((floor($seconds / 3600) * 3600))) / 60 : ($seconds / 60),($seconds % 60)));
      }
    }

  }
