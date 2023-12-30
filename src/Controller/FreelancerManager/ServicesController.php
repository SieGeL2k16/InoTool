<?php declare(strict_types=1);
/**
 * Handles additional services to be added like travel allowance, Hotel fees etc.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (30-Dec-2023)
 */

namespace App\Controller\FreelancerManager;

use App\Entity\FlProjectEntries;
use App\Entity\FlProjects;
use App\Repository\FlProjectsRepository;
use App\Service\serviceHelper;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_USER")]
#[Route("/freelancer/services/")]
class ServicesController extends AbstractController
  {
  const ACTNAV  = 'services';
  
  /**
   * Lists selection of all currently supported services to be added to a given project.
   * @param FlProjectsRepository $projectsRepository
   * @return Response
   */
  #[Route("overview", name: "fl_services_overview")]
  public function showServices(FlProjectsRepository $projectsRepository):Response
    {
    $user = $this->getUser();
    return $this->render('freelancermanager/services_overview.html.twig',[
      'ACTNAV'        => self::ACTNAV,
      'PROJECTS_LIST' => $projectsRepository->findBy(['RefUser' => $user,'Status' => FlProjects::PROJ_STATUS_ACTIVE],['ProjectName' => 'asc']),
      'SERVICES_LIST' => serviceHelper::$supportedServices,
      ]);
    }
  
  /**
   * Returns config template based on given RecordType ID
   * @param Request $request
   * @return JsonResponse
   * @throws Exception
   */
  #[Route("cfgloader",name: "fl_services_ajax_cfgload",methods: ["POST"])]
  public function loadConfigTemplate(Request $request):JsonResponse
    {
    $rtid = $request->get('rtid');
    $template = '<div class="placeholder w-100"></div>';
    switch($rtid)
      {
      case  FlProjectEntries::RT_TRAVEL_ALLOWANCE:
        $template = $this->render('freelancermanager/'.serviceHelper::$serviceConfigs[$rtid])->getContent();
        break;

      case  FlProjectEntries::RT_PROJECT_ENTRY:
        throw new Exception("WEIRD - YOU SHALL NOT BE THERE!?");
      }
    return new JsonResponse(['HTML' => $template]);
    }
  
  /**
   * Saves service as project entry with specific record type based on user's selection.
   * @param Request $request
   * @param FlProjectsRepository $projectsRepository
   * @param EntityManagerInterface $em
   * @param LoggerInterface $logger
   * @return Response
   * @throws Exception
   */
  #[Route("save",name: "fl_services_save", methods: ["POST"])]
  public function save(Request $request, FlProjectsRepository $projectsRepository,
                       EntityManagerInterface $em, LoggerInterface $logger):Response
    {
    $pid = $request->get('fl_project');
    $srv = $request->get('fl_service');
    $fd  = new DateTime($request->get('fl_ta_date'));
    $sum = str_replace(",",".",$request->get('fl_ta_sum'));
    $pe  = (new FlProjectEntries())
      ->setRecordType((int)$srv)
      ->setEntryDate($fd)
      ->setRefUser($this->getUser())
      ->setRefProject($projectsRepository->find($pid))
      ->setCreatedOn(new DateTimeImmutable())
      ->setCosts($sum)
      ->setStatus(FlProjectEntries::STATUS_ACTIVE)
      ->setWorkDescription($request->get('wdesc'))
      ;
    $em->persist($pe);
    $em->flush();
    $logger->info("Successfully saved RecordType=".serviceHelper::$supportedServices[$srv]." on {$fd->format('Ymd')}");
    $this->addFlash('success',serviceHelper::$supportedServices[$srv]." wurde erfolgreich für den {$fd->format('Ymd')} gespeichert.");
    return $this->redirectToRoute("fl_time_form",['date' => $fd->format('Ymd')]);
    }
  
  }
