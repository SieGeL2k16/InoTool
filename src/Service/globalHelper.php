<?php declare(strict_types=1);
/**
 * Helper service with methods used globally
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (26-Nov-2022)
 */

namespace App\Service;

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
  
  }
