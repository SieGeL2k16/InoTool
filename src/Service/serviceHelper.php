<?php declare(strict_types=1);
/**
 * Helper methods for services
 */

namespace App\Service;

use App\Entity\FlProjectEntries;

class serviceHelper
  {
  /**
   * All supported services one can add to any project
   * @var array|string[]
   */
  public static array $supportedServices = [
    FlProjectEntries::RT_TRAVEL_ALLOWANCE   => 'Fahrkostenpauschale',
    ];
  
  /**
   * List of config templates for all supported services
   * @var array|string[]
   */
  public static array $serviceConfigs = [
    FlProjectEntries::RT_TRAVEL_ALLOWANCE   => '_services_travel_allowance.html.twig',
    ];
  
  }
