<?php declare(strict_types=1);
/**
 * Handles configuration for the current user.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (13-Nov-2022)
 */

namespace App\Controller\FreelancerManager;

use App\Entity\FlConfiguration;
use App\Repository\FlConfigurationRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_USER')]
#[Route("/freelancer/configuration/")]
class ConfigurationController extends AbstractController
  {
  const ACTNAV = 'config';
  
  /** @var EntityManagerInterface $entity */
  private EntityManagerInterface $entity;
  
  public function __construct(EntityManagerInterface $entity)
    {
    $this->entity = $entity;
    }
  
  /**
   * Shows configuration screen.
   * @return Response
   */
  #[Route("form",name: "fl_configuration_form")]
  public function form():Response
    {
    return $this->render('freelancermanager/configuration.html.twig', [
      'ACTNAV'  => self::ACTNAV,
      'CONFIG'  => $this->loadConfig(),
      ]);
    }
  
  /**
   * Saves configuration for a given user
   * @return Response
   */
  #[Route("save",name: "fl_configuration_save",methods: ['POST'])]
  public function save(Request $request):Response
    {
    $config = $this->loadConfig();
    $config->setBankName($request->get('bankName'))
      ->setBankAccount($request->get('bankAccount'))
      ;
    dd($_REQUEST);
    }
  
  /**
   * Returns a configuration object, either loaded from disc or a new one with current user assigned.
   * @return FlConfiguration
   */
  private function loadConfig():FlConfiguration
    {
    $user = $this->getUser();
    $config = $this->entity->getRepository(FlConfiguration::class)->findOneBy(['RefUser' => $user]);
    if($config === null)
      {
      $config = new FlConfiguration();
      $config->setRefUser($user);
      }
    return $config;
    }
  }
