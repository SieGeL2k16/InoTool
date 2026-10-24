<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Common base class for all controllers.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (24-10-2026)
 */
abstract class BaseController extends AbstractController
  {
  /**
   * Returns the logged-in user as application entity.
   * @return User|null
   */
  protected function getUser(): ?User
    {
    $user = parent::getUser();
    return $user instanceof User ? $user : null;
    }
  }
