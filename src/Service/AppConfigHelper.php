<?php declare(strict_types=1);
/**
 * Service for AppConfig functionality.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (13-Nov-2021)
 */

namespace App\Service;

use App\Entity\AppConfig;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
   * @param User|UserInterface|null $user
   * @return AppConfig|null
   */
  public function GetObject(string $key, User|UserInterface|null $user = null):?AppConfig
    {
    return $this->entity->getRepository(AppConfig::class)->findOneBy(['RefUser' => $user, 'KeyName' => $key]);
    }

    /**
     * Returns value for a given key/user combo.
     * @param string $key
     * @param string|null $default
     * @param User|UserInterface|null $user
     * @return string|null
     */
  public function Get(string $key, ?string $default = null, User|UserInterface|null $user = null):?string
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
     * @param User|UserInterface|null $user
     */
  public function Set(string $key, string $val, User|UserInterface|null $user = null): void
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
   * Removes a config item.
   * @param string $key Config item to remove
   * @param User|UserInterface|null $user Opt. user
   */
  public function Del(string $key, User|UserInterface|null $user = null): void
    {
    $obj = $this->GetObject($key, $user);
    if($obj === null)
      {
      return;
      }
    $this->entity->remove($obj);
    $this->entity->flush();
    }

  /**
   * Returns configuration values as associative array for the given key names.
   * @param array $fields
   * @param UserInterface|null $user
   * @return array
   */
  public function xGet(array $fields,?UserInterface $user = null):array
    {
    $ret = [];
    foreach($fields as $f)
      {
      $ret[$f] = null;
      }
    /** @var AppConfig $item */
    foreach($this->entity->getRepository(AppConfig::class)->getListByKeys($fields,$user) as $item)
      {
      $ret[$item->getKeyName()] = $item->getValue();
      }
    return $ret;
    }

  }
