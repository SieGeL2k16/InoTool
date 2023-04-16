<?php declare(strict_types=1);
/**
 * Helper service with methods used globally
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (26-Nov-2022)
 */

namespace App\Service;

use DateTime;
use IntlDateFormatter;

class globalHelper
  {
  /**
   * Method parses the "order" array of Datatables (with sanity checks)
   * and returns an ORDER BY <colnr> <direction> string
   * @param array $order
   * @param int $maxcols
   * @return string
   */
  public static function parseDtOrder(array $order, int $maxcols):string
    {
    $ostring = "";
    foreach($order as $o)
      {
      if((int)$o['column'] > $maxcols)
        {
        $f = '1';
        }
      else
        {
        $f = sprintf("%s",(int)$o['column']+1);   // DB always start with 1 !
        }
      if(in_array(strtolower($o['dir']),['asc','desc']) === false)
        {
        $fd = 'asc';
        }
      else
        {
        $fd = $o['dir'];
        }
      $ostring.=sprintf("%s %s,",$f,$fd);
      }
    return rtrim($ostring,",");
    }
  
  /**
   * Returns timezone string, falls back to Europe/Berlin if nothing can be found
   * @return string
   */
  public static function getTZ():string
    {
    $tz = ini_get("date.timezone");
    if(empty($tz) === true)
      {
      $tz = "Europe/Berlin";    // Fallback
      }
    return $tz;
    }
  
  /**
   * Returns list of localized names for the given locale
   * @param string $locale
   * @return array
   */
  public function getLocalizedMonths(string $locale):array
    {
    $arr = [];
    $fmt = new IntlDateFormatter($locale, IntlDateFormatter::FULL, IntlDateFormatter::FULL, self::getTZ(), IntlDateFormatter::GREGORIAN, 'MMM');
    for($sm = 1; $sm < 13; $sm++)
      {
      $dt = new DateTime("01-$sm-2023 00:00:00");
      $arr[$sm] = $fmt->format($dt);
      }
    return $arr;
    }
  }
