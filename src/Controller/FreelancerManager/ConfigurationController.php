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
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

  /** @var LoggerInterface $logger */
  private LoggerInterface $logger;

  /**
   * Constructor
   * @param EntityManagerInterface $entity
   * @param LoggerInterface $logger
   */
  public function __construct(EntityManagerInterface $entity, LoggerInterface $logger)
    {
    $this->entity = $entity;
    $this->logger = $logger;
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
   * @param Request $request
   * @return Response
   */
  #[Route("save",name: "fl_configuration_save",methods: ['POST'])]
  public function save(Request $request):Response
    {
    $config = $this->loadConfig();
    /** @var UploadedFile $pic */
    $pic = $request->files->get('companyLogo');

    if($pic != null)
      {
      $config
        ->setCompanyLogo(file_get_contents($pic->getRealPath()))
        ->setCompanyLogoMimeType($pic->getClientMimeType())
        ->setCompanyLogoName($pic->getClientOriginalName())
        ->setCompanyLogoSize($pic->getSize())
        ;
      }
    $config
      ->setBankName($request->get('bankName'))
      ->setBankAccount($request->get('bankAccount'))
      ->setBankBic($request->get('bankBic'))
      ->setBankIban($request->get('bankIban'))
      ->setCompanyName($request->get('companyName'))
      ->setCompanyOwner($request->get('companyOwner'))
      ->setCompanyTaxNumber($request->get('companyTaxNumber'))
      ->setCompanyEmail($request->get('companyEmail'))
      ->setCompanyStreet($request->get('companyStreet'))
      ->setCompanyPostal($request->get('companyPostal'))
      ->setCompanyCity($request->get('companyCity'))
      ->setCompanyCountry($request->get('companyCountry'))
      ->setCompanyPhone($request->get('companyPhone'))
      ->setCompanyFax($request->get('companyFax'))
      ;
    try
      {
      $this->entity->persist($config);
      $this->entity->flush();
      $this->addFlash('success','Einstellungen wurden gespeichert.');
      $this->logger->info("Configuration was successfully saved");
      }
    catch(Exception $e)
      {
      $this->addFlash('error',"Einstellungen konnten nicht gespeichert werden: ".$e->getCode());
      $this->logger->critical(__METHOD__.": ".$e->getMessage());
      }
    return $this->redirectToRoute("fl_configuration_form");
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

  /**
   * Removes Company Logo from configuration
   * @param EntityManagerInterface $entityManager
   * @return Response
   */
  #[Route("delete-logo",name: "fl_configuration_delete_logo",methods: ["POST"])]
  public function RemoveLogo(EntityManagerInterface $entityManager):Response
    {
    $user = $this->getUser();
    $config = $entityManager->getRepository(FlConfiguration::class)->findOneBy(['RefUser' => $user]);
    if($config === null)
      {
      $this->logger->warning("Tried to remove logo of non-existant configuration item?!");
      return $this->redirectToRoute("fl_configuration_form");
      }
    $config->setCompanyLogo(null)
      ->setCompanyLogoName(null)
      ->setCompanyLogoMimeType(null)
      ->setCompanyLogoSize(null);
    $entityManager->persist($config);
    $entityManager->flush();
    $this->addFlash('success','Logo wurde gelöscht');
    $this->logger->info('companyLogo was removed');
    return $this->redirectToRoute("fl_configuration_form");
    }
  }
