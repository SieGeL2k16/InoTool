<?php declare(strict_types=1);
/**
 * Category related methods.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 0.1 (08-Jan-2022)
 */

namespace App\Controller\KontoManager;

use App\Entity\AccountCategories;
use App\Entity\AccountData;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use PDOException;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_USER')]
class CategoryController extends AbstractController
  {
  const ACT_NAV = 'categories';

  private ManagerRegistry $registry;
  private LoggerInterface $logger;
  
  public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
    $this->registry = $registry;
    $this->logger = $logger;
    }
  
  /**
   * Shows list of alle categories owned by current user.
   * @return Response
   */
  #[Route('/kontomanager/kategorien',name: 'km_categories')]
  public function list():Response
    {
    /** @var User $user */
    $user = $this->getUser();
    return $this->render('kontomanager/categories_list.html.twig',[
      'ACTNAV'        => self::ACT_NAV,
      'NO_CAT_COUNT'  => $this->registry->getRepository(AccountData::class)->count(['RefUser' => $user,'RefCategory' => null]),
      'CATEGORY_LIST' => $this->registry->getRepository(AccountCategories::class)->GetFullCategoryList($user),
      ]);
    }
  
  /**
   * Add / Edit category entry
   * @param int $id
   * @return Response
   */
  #[Route('/kontomanager/kategorien/form/{id}',name: 'km_category_form')]
  public function Form(int $id = 0):Response
    {
    /** @var User $user */
    $user = $this->getUser();
    if($id)
      {
      $ac = $this->registry->getRepository(AccountCategories::class)->findOneBy(['RefUser' => $user,'id' => $id]);
      if($ac === null)
        {
        $this->addFlash('error',"Kategorie mit ID=$id nicht gefunden?!");
        $this->logger->error("No category found with ID=$id");
        return $this->redirectToRoute('km_categories');
        }
      }
    else
      {
      $ac = new AccountCategories();
      $ac->setRefUser($user);
      }
    return $this->render('kontomanager/categories_form.html.twig',[
      'ACTNAV'  => self::ACT_NAV,
      'ENTRY'   => $ac,
      ]);
    }
  
  /**
   * Saves category data
   * @param Request $request
   * @return Response
   */
  #[Route('/kontomanager/kategorien/speichern',name: 'km_category_save',methods: ['POST'])]
  public function Save(Request $request):Response
    {
    /** @var User $user */
    $user = $this->getUser();
    $catid= (int)$request->get('CATID');
    try
      {
      if($catid)
        {
        $cat = $this->registry->getRepository(AccountCategories::class)->findOneBy(['RefUser' => $user,'id' => $catid]);
        $flash = "Kategorie #$catid aktualisiert";
        $logmsg= "Updated category #$catid";
        }
      else
        {
        $cat = new AccountCategories();
        $cat->setRefUser($user);
        $flash = "Neue Kategorie angelegt";
        $logmsg= "Added new category";
        }
      $cat->setName($request->get('catname'))
        ->setIsActive((bool)$request->get('is_active'))
        ->setIsFixed((bool)$request->get('is_fixed'))
        ;
      $this->registry->getManager()->persist($cat);
      $this->registry->getManager()->flush();
      $this->addFlash('info',$flash);
      $this->logger->info($logmsg);
      }
    catch(Exception | PDOException $e)
      {
      $this->addFlash('warning','Speicherfehler ('.$e->getCode().') aufgetreten!');
      $this->logger->error("Category/Save($catid): ".$e->getMessage());
      }
    return $this->redirectToRoute('km_categories');
    }
  
  /**
   * Removes an category entry and resets all assigned account_data values to null
   * @param Request $request
   * @return Response
   */
  #[Route('/kontomanager/kategorien/loeschen',name: 'km_category_del',methods: ['POST'])]
  public function Delete(Request $request):Response
    {
    /** @var User $user */
    $user = $this->getUser();
    $catid = (int)$request->get('CATID');
    $catobj= $this->registry->getRepository(AccountCategories::class)->findOneBy(['RefUser' => $user, 'id' => $catid]);
    if($catobj === null)
      {
      $this->addFlash('error',"Kategorie mit ID=$catid nicht gefunden - Löschen nicht möglich!");
      $this->logger->error("Cannot remove category with ID=$catid: not found");
      return $this->redirectToRoute('km_categories');
      }
    $this->registry->getRepository(AccountCategories::class)->Delete($user,$catid);
    $this->addFlash('info',"Kategorie \"{$catobj->getName()}\" wurde gelöscht");
    $this->logger->error("Removed category {$catobj->getName()}");
    return $this->redirectToRoute('km_categories');
    }
  
  }
