<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Main index page.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (25-12-2021)
 */
class IndexController extends AbstractController
  {
  /**
   * Shows login screen
   * @return Response
   */
  #[Route('/',name: 'index')]
  public function index():Response
    {
    return $this->render('index.html.twig');
    }
    
  }
