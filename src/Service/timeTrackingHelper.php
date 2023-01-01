<?php
/**
 * Helper methods for Time tracking module.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (01-Jan-2023)
 */

namespace App\Service;

class timeTrackingHelper
  {
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
    $maxdays = (int)date("t",mktime(2,0,0,$month,1,$year));
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
  
  }
