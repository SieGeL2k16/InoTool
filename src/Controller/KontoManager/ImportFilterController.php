<?php declare(strict_types=1);
/**
 * Handles import filter.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (15-Jul-2022)
 */

namespace App\Controller\KontoManager;

use App\Entity\AccountCategories;
use App\Entity\AccountImportFilter;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use PDOException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ImportFilterController extends AbstractController
  {
  const ACT_NAV = 'filter';

  /** @var ManagerRegistry $registry */
  private ManagerRegistry $registry;
  
  /** @var LoggerInterface $logger */
  private LoggerInterface $logger;
  
  public function __construct(ManagerRegistry $registry,LoggerInterface $logger)
    {
    $this->registry = $registry;
    $this->logger = $logger;
    }
  
  /**
   * Displays all currently registered import filter
   * @return Response
   */
  #[Route('/kontomanager/importfilter',name: 'km_importfilter')]
  public function index():Response
    {
    $user = $this->getUser();
    $iflist = $this->registry->getRepository(AccountImportFilter::class)->findBy(['RefUser' => $user],['Name' => 'asc']);
    return $this->render('kontomanager/importfilter_list.html.twig', [
      'ACTNAV'    => self::ACT_NAV,
      'IFLIST'    => $iflist,
      ]);
    }
  
  /**
   * Add / Edit importfilter entry
   * @param int $id
   * @return Response
   */
  #[Route('/kontomanager/importfilter/form/{id}',name: 'km_importfilter_form')]
  public function Form(int $id = 0):Response
    {
    /** @var User $user */
    $user = $this->getUser();
    if($id === 0)
      {
      $if = new AccountImportFilter();
      $if->setRefUser($user)->setCreatedOn(new DateTimeImmutable());
      }
    else
      {
      $if = $this->registry->getRepository(AccountImportFilter::class)->findOneBy(['RefUser' => $user, 'id' => $id]);
      if($if === null)
        {
        $this->addFlash('error',"Keinen Eintrag mit ID = $id gefunden?!");
        $this->logger->error("No importfilter entry found with ID=$id");
        return $this->redirectToRoute('km_importfilter');
        }
      }
    return $this->render("kontomanager/importfilter_form.html.twig",[
      'ACTNAV'    => self::ACT_NAV,
      'ENTRY'     => $if,
      'CATLIST'   => $this->registry->getRepository(AccountCategories::class)->getCategoryList($user),
      ]);
    }
  
  /**
   * Saves category data
   * @param Request $request
   * @return Response
   */
  #[Route('/kontomanager/importfilter/speichern',name: 'km_importfilter_save',methods: ['POST'])]
  public function Save(Request $request):Response
    {
    /** @var User $user */
    $user = $this->getUser();
    $ifid = (int)$request->get('ID');
    try
      {
      if($ifid)
        {
        $if = $this->registry->getRepository(AccountImportFilter::class)->findOneBy(['RefUser' => $user,'id' => $ifid]);
        $flash = "Eintrag #$ifid aktualisiert";
        $logmsg= "Updated importfilter #$ifid";
        }
      else
        {
        $if = new AccountImportFilter();
        $if->setRefUser($user)->setCreatedOn(new DateTimeImmutable());
        $flash = "Neuen Importfilter angelegt";
        $logmsg= "Added new importfilter";
        }
      $if->setName($request->get('ifname'))
        ->setIsActive((bool)$request->get('is_active'))
        ->setDefinition($request->get('ifdef'))
        ->setRefCategory($this->registry->getRepository(AccountCategories::class)->find((int)$request->get('catid',0)))
        ;
      $this->registry->getManager()->persist($if);
      $this->registry->getManager()->flush();
      $this->addFlash('info',$flash);
      $this->logger->info($logmsg);
      }
    catch(Exception | PDOException $e)
      {
      $this->addFlash('warning','Speicherfehler ('.$e->getCode().') aufgetreten!');
      $this->logger->error(__METHOD__.": ".$e->getMessage());
      }
    return $this->redirectToRoute('km_importfilter');
    }
  
  /**
   * Removes an importfilter entry
   * @param Request $request
   * @return Response
   */
  #[Route('/kontomanager/importfilter/loeschen',name: 'km_importfilter_del',methods: ['POST'])]
  public function Delete(Request $request):Response
    {
    /** @var User $user */
    $user = $this->getUser();
    $ifid = (int)$request->get('ID');
    $ifobj= $this->registry->getRepository(AccountImportFilter::class)->findOneBy(['RefUser' => $user, 'id' => $ifid]);
    if($ifobj === null)
      {
      $this->addFlash('error',"Importfilter mit ID=$ifid nicht gefunden - Löschen nicht möglich!");
      $this->logger->error("Cannot remove importfilter with ID=$ifid: not found");
      return $this->redirectToRoute('km_importfilter');
      }
    $this->registry->getRepository(AccountImportFilter::class)->Delete($user,$ifid);
    $this->addFlash('info',"Importfilter \"{$ifobj->getName()}\" wurde gelöscht");
    $this->logger->error("Removed importfilter {$ifobj->getName()}");
    return $this->redirectToRoute('km_importfilter');
    }
  }
