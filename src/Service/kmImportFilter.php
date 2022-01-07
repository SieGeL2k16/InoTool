<?php
/**
 * Methods for handling import filter.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (07-Jan-2022)
 */

namespace App\Service;

use App\Entity\AccountCategories;
use App\Entity\AccountImportFilter;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;

class kmImportFilter
  {
  /**
   * Holds all stored filter definitions
   * @var array
   */
  private array $filter;
  
  /**
   * @var array Holds all available categories
   */
  private array $categories;

  /** @var ManagerRegistry $registry */
  private ManagerRegistry $registry;
  
  /**
   * @param ManagerRegistry $registry
   */
  public function __construct(ManagerRegistry $registry)
    {
    $this->registry = $registry;
    }
  
  /**
   * Pre-loads filter and category objects for faster access.
   * @param User $user
   * @return void
   */
  public function Init(User $user)
    {
    foreach($this->registry->getRepository(AccountImportFilter::class)->findBy(['RefUser' => $user,'IsActive' => true]) as $item)
      {
      $this->filter[] = $item;
      }
    foreach($this->registry->getRepository(AccountCategories::class)->findBy(['RefUser' => $user]) as $item)
      {
      $this->categories[$item->getId()] = $item->getName();
      }
    }

  /**
   * Tests if a given Accounting text matches one of our filters
   * @param string $acctext The accounting text
   * @return int 0 If no match was found, else the corresponding category id
   */
  public function CheckFilter(string $acctext): int
    {
    /** @var AccountImportFilter $data */
      foreach($this->filter as $data)
      {
      $pattern = sprintf("/%s/i",preg_quote($data->getDefinition(),'/'));
      if(preg_match($pattern,$acctext))
        {
        return $data->getRefCategory()->getId();
        }
      }
    return 0;
    }
  
  /**
   * Returns the category name for a given category id.
   * @param integer $id The Category ID to check
   * @return string Empty string if catid does not exist, else the
   * corresponding category name.
   */
  public function GetCatnameById(int $id): string
    {
    if(isset($this->categories[$id]) === FALSE)
      {
      return '';
      }
    return $this->categories[$id] ;
    }
  }
