<?php declare(strict_types=1);
/**
 * All customer related methods.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (13-Nov-2022)
 */

namespace App\Controller\FreelancerManager;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_USER')]
#[Route("/freelancer/customer/")]
class CustomerController extends AbstractController
  {
  const ACTNAV = 'customer';
  
  /**
   * Lists all customer currently registered for current user.
   * @return Response
   */
  #[Route("list",name: "fl_customer_list")]
  public function list():Response
    {
    return $this->render('freelancermanager/customer_list.html.twig',[
      'ACTNAV' => self::ACTNAV,
      ]);
    }
  }
