<?php
/**
 * Startpage with dashboard for Freelancer Manager.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (26-12-2021)
 */

namespace App\Controller\FreelancerManager;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
  {
  #[Route('/freelancer',name: 'flm_index')]
  public function index():Response
    {
    return $this->render('freelancermanager/index.html.twig');
    }
  }
