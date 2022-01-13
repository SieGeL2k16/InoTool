<?php declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\RuntimeExtensionInterface;

/**
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
  }
