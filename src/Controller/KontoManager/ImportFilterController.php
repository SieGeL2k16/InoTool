<?php
/**
 * Handles import filter.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (15-Jul-2022)
 */

namespace App\Controller\KontoManager;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_USER')]
class ImportFilterController extends AbstractController
  {
  const ACT_NAV = 'filter';

  /**
   * @return Response
   */
  #[Route('/kontomanager/importfilter',name: 'km_importfilter')]
  public function index():Response
    {
  
    return $this->render('kontomanager/importfilter_list.html.twig', [
      'ACTNAV'        => self::ACT_NAV,

    ]);
    }
    
  }
