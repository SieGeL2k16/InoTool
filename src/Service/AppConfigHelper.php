<?php declare(strict_types=1);
/**
 * Service for AppConfig functionality.
 * @package p-man
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (13-Nov-2021)
 */

namespace App\Service;

use App\Entity\AppConfig;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AppConfigHelper
  {
  private EntityManagerInterface $entity;
  
  /**
   * @param EntityManagerInterface $entity
   */
  public function __construct(EntityManagerInterface $entity)
    {
    $this->entity = $entity;
    }
  
  /**
   * Returns AppConfig object, null if nothing is found
   * @param string $key
   * @param User|null $user
   * @return AppConfig|null
   */
  public function GetObject(string $key, User $user = null):?AppConfig
    {
    return $this->entity->getRepository(AppConfig::class)->findOneBy(['RefUser' => $user, 'KeyName' => $key]);
    }
  
  /**
   * Returns value for a given key/user combo.
   * @param string $key
   * @param string|null $default
   * @param User|null $user
   * @return string|null
   */
  public function Get(string $key, string $default = null, User $user = null):?string
    {
    $obj = $this->GetObject($key,$user);
    if($obj !== null)
      {
      return $obj->getValue();
      }
    return $default;
    }
  
  /**
   * Add or update configuration item $key with value $val for user $user.
   * @param string $key
   * @param string $val
   * @param User|null $user
   */
  public function Set(string $key, string $val, User $user = null)
    {
    $obj = $this->GetObject($key,$user);
    if($obj === null)
      {
      $obj = new AppConfig();
      $obj->setRefUser($user)->setKeyName($key);
      }
    $obj->setValue($val);
    $this->entity->persist($obj);
    $this->entity->flush();
    }
  
  /**
   * Removes an config item.
   * @param string $key Config item to remove
   * @param User|null $user Opt. user
   */
  public function Del(string $key, User $user = null)
    {
    $obj = $this->GetObject($key, $user);
    if($obj === null)
      {
      return;
      }
    $this->entity->remove($obj);
    $this->entity->flush();
    }
  
  }
