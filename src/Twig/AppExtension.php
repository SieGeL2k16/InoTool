<?php declare(strict_types=1);
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class AppExtension
 * @package App\Twig
 */
class AppExtension extends AbstractExtension
  {
  
  public function __construct()
    {
    }
  
  /**
   * @return TwigFilter[]
   */
  public function getFilters(): array
    {
    return [
      new TwigFilter('YesNo'                  , [AppRuntime::class,   'YesNo']),
      new TwigFilter('EmptyString'            , [AppRuntime::class,   'EmptyString']),
      new TwigFilter('FormatCheckbox'         , [AppRuntime::class,   'FormatCheckbox']),
      new TwigFilter('YesNoText'              , [AppRuntime::class,   'YesNoText']),
      new TwigFilter('MoneyColor'             , [AppRuntime::class,   'MoneyColor'])
      ];
    }

  /**
   * @return TwigFunction[]
   */
  public function getFunctions(): array
  {
    return [

      /**
       * Allows to set a key/value in associative array from Twig
       * Taken from https://stackoverflow.com/a/44756237/3874598
       */
      new TwigFunction('set_element', function ($data, $key, $value) {
        // Assign value to $data[$key]
        if (!is_array($data)) {
          return $data;
        }
        $data[$key] = $value;
        return $data;
      }),
      
      ];
    }
 
  }
