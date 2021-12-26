<?php declare(strict_types=1);
/**
 * Main controller for KontoManager.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (26-12-2021)
 */

namespace App\Controller\KontoManager;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
  {

  #[Route('/kontomanager',name: 'km_index')]
  public function index():Response
    {
    return $this->render('kontomanager/index.html.twig');
    }
    
  }
