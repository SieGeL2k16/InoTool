<?php declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\RuntimeExtensionInterface;

/**
 * Additional twig filter and functions - implementation.
 * Class AppRuntime
 * @package App\Twig
 */
class AppRuntime implements RuntimeExtensionInterface
  {
  /**
   * AppRuntime constructor.
   */
  public function __construct()
    {
    }

  /**
   * @param bool $val
   * @return string
   */
  public function YesNo(bool $val = null):string
    {
    if($val === true)
      {
      return '<i class="far fa-smile text-success"></i> ';
      }
    return '<i class="far fa-frown text-danger"></i> ';
    }

  /**
   * @param string|null $val
   * @return string
   */
  public function EmptyString(?string $val):string
    {
    if($val === null || $val === "")
      {
      return '---';
      }
    return $val;
    }

  /**
   * Formats checkbox value
   * @param int|null $val
   * @return string
   */
  public function FormatCheckbox(?int $val):string
    {
    if($val)
      {
      return '<i class="fa-solid fa-check text-success"></i>';
      }
    return '<i class="fa-solid fa-xmark text-danger"></i>';
    }
  
  
  /**
   * Simple text-based wrapper for 0/1 flags
   * @param int $val
   * @return string
   */
  public function YesNoText(int $val):string
    {
    if(!$val)
      {
      return 'Nein';
      }
    return 'Ja';
    }
  
  /**
   * Returns bootstrap class based on money < 0
   * @param float $money
   * @return string
   */
  public function MoneyColor(float $money):string
    {
    if($money < 0.00)
      {
      return 'text-danger';
      }
    return 'text-success';
    }

  /**
   * Wrapper for Base64Encode(), can be used with strings or stream resources
   * @param string $raw
   * @return string
   */
  public function Base64Encode(mixed $raw):string
    {
    if(is_string($raw) === true)
      {
      return base64_encode($raw);
      }
    return base64_encode(stream_get_contents($raw));
    }
  
  /**
   * Returns fontawesome icons for postgres bool return "true" and "false"
   * @param string $bool
   * @return string
   */
  public function YesNoPgBool(string $bool):string
    {
    if(mb_strtolower($bool) === 'true')
      {
      return $this->YesNo(true);
      }
    return $this->YesNo(false);
    }
  
  /**
   * Adds classes based on given date.
   * Current date i.e. will add classes "text-success fw-bold"
   * @param mixed $day Day (01 - 31)
   * @param mixed $year Year
   * @param mixed $mon Month
   * @param array|null $entries Pass in an associative array with key in format "Ymd" and value the number of entries.
   * @return string
   */
  public function calcClasses(mixed $day,mixed $year,mixed $mon, array $entries = null):string
    {
    $cl = "";
    if($day === null)
      {
      return "";
      }
    if(date('Ymd') === sprintf("%04d%02d%02d",$year,$mon,$day))
      {
      $cl.=" text-success fw-bold";
      }
    $cl.=" bg-light";
    return $cl;
    }
  }
