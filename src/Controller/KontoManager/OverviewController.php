<?php declare(strict_types=1);
/**
 * Account Data browser with edit functionality.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (01-Jan-2022)
 */

namespace App\Controller\KontoManager;

use App\Entity\AccountBankAccounts;
use App\Entity\AccountCategories;
use App\Entity\AccountData;
use App\Entity\User;
use App\Service\AppConfigHelper;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use IntlDateFormatter;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_USER')]
class OverviewController extends AbstractController
  {
  const ACT_NAV = 'list';
  const ACT_NAV_ADD = 'add';
  
  /**
   * @param Request $request
   * @param ManagerRegistry $registry
   * @param AppConfigHelper $appConfigHelper
   * @return Response
   * @throws Exception
   */
  #[Route('/kontomanager/liste',name: 'km_list')]
  public function index(Request $request, ManagerRegistry $registry, AppConfigHelper $appConfigHelper):Response
    {
    /** @var User $user */
    $user       = $this->getUser();
    $f_category = $request->get('f_cat');
    $f_month    = $request->get('f_month');
    $f_year     = $request->get('f_year');
    $f_search   = $request->get('f_search');
    if($f_category === null)
      {
      $f_category = $appConfigHelper->Get(User::CFG_ACC_BROWSER_F_CAT,"-1",$user);
      }
    else
      {
      $appConfigHelper->Set(User::CFG_ACC_BROWSER_F_CAT,(string)$f_category,$user);
      }
    if($f_month === null)
      {
      $f_month = $appConfigHelper->Get(User::CFG_ACC_BROWSER_F_MONTH,date('m'),$user);
      }
    else
      {
      $appConfigHelper->Set(User::CFG_ACC_BROWSER_F_MONTH,(string)$f_month,$user);
      }
    if($f_year === null)
      {
      $f_year = $appConfigHelper->Get(User::CFG_ACC_BROWSER_F_YEAR,date('Y'),$user);
      }
    else
      {
      $appConfigHelper->Set(User::CFG_ACC_BROWSER_F_YEAR,(string)$f_year,$user);
      }
    // @todo Make locale configurable / read from user's setting
    $dfmt = new IntlDateFormatter( "de-DE" ,IntlDateFormatter::FULL, IntlDateFormatter::NONE,null,null,"MMMM");
    $month_list = [];
    for($i = 1; $i < 13; $i++)
      {
      $dnow = new DateTime(sprintf("%04d-%02d-01",date("Y"),$i));
      $month_list[$i] = $dfmt->format($dnow);
      }
    $dbstats = $registry->getRepository(AccountData::class)->GetDatabaseStatistics($user);
    $Ymin = (new DateTime($dbstats['MIN_DATE']))->format('Y');
    $Ymax = date('Y');    // Generate year selection up to current year
    $year_list = [];
    for($i = (int)$Ymax; $i > (int)$Ymin-1; $i--)
      {
      $year_list[] = $i;
      }
    $data = $registry->getRepository(AccountData::class)->GetBrowserData($user,['F_CATEGORY' => $f_category,'F_MONTH' => $f_month,'F_YEAR' => $f_year,'F_SEARCH' => '']);
    $revenue = ['INCOME' => 0.00, 'OUTCOME' => 0.00,'DIFF' => 0.00];
    foreach($data as $item)
      {
      if($item['is_income'])
        {
        $key = 'INCOME';
        }
      else
        {
        $key = 'OUTCOME';
        }
      $revenue[$key]+=$item['amount'];
      }
    $revenue['DIFF'] = abs($revenue['INCOME']) - abs($revenue['OUTCOME']);
    return $this->render('kontomanager/list.html.twig',[
      'ACTNAV'        => self::ACT_NAV,
      'CATEGORYLIST'  => $registry->getRepository(AccountCategories::class)->getCategoryList($user),
      'MONTHLIST'     => $month_list,
      'YEARLIST'      => $year_list,
      'F_CATEGORY'    => $f_category,
      'F_MONTH'       => $f_month,
      'F_YEAR'        => $f_year,
      'DATA'          => $data,
      'DATA_COUNT'    => count($data),
      'TOTAL_COUNT'   => $dbstats['TOTAL_ROWS'],
      'REVENUE'       => $revenue,
      ]);
    }
  
  /**
   * Saves changed category entry.
   * @param Request $request
   * @param ManagerRegistry $registry
   * @param LoggerInterface $logger
   * @return Response
   * @throws Exception
   */
  #[Route('/kontomanager/kategorie_aendern',name: 'km_catSaver')]
  public function CatSaver(Request $request,ManagerRegistry $registry, LoggerInterface $logger):Response
    {
    /** @var User $user */
    $user = $this->getUser();
    $id   = (int)$request->get('accid');
    $cid  = (int)$request->get('newcat');
    $registry->getRepository(AccountData::class)->CatSaver($user,$id,$cid);
    $logger->info("Updated category of entry #{$id}");
    $this->addFlash('info','Kategorie wurde aktualisiert');
    return $this->redirectToRoute('km_list',['_fragment' => 'R'.$id]);
    }
  
  /**
   * Calls the form to create new entry or modify existing entry.
   * @param ManagerRegistry $registry
   * @param LoggerInterface $logger
   * @param int $id
   * @return Response
   * @throws Exception
   */
  #[Route('/kontomanager/form/{id}',name: 'km_accForm',requirements: ['id' => '\d+'])]
  public function Form(ManagerRegistry $registry, LoggerInterface $logger, int $id = 0):Response
    {
    /** @var User $user */
    $user = $this->getUser();
    if(!$id)
      {
      $entry = new AccountData();
      $entry->setRefUser($user);
      $ACTNAV = self::ACT_NAV_ADD;
      $title = "Kontoeintrag hinzufügen";
      }
    else
      {
      $entry = $registry->getRepository(AccountData::class)->findOneBy(['RefUser' => $user, 'id' => $id]);
      if($entry === null)
        {
        $this->addFlash('warning',"Eintrag #$id kann nicht bearbeitet werden!");
        $logger->warning("No accounting data found for ID=$id ?!");
        return $this->redirectToRoute('km_list');
        }
      $ACTNAV = self::ACT_NAV;
      $title = "Kontoeintrag bearbeiten";
      }
    return $this->render('kontomanager/account_data_form.html.twig',[
      'ACTNAV'    => $ACTNAV,
      'ENTRY'     => $entry,
      'TITLE'     => $title,
      'BANKLIST'  => $registry->getRepository(AccountBankAccounts::class)->findBy(['RefUser' => $user]),
      'CATLIST'   => $registry->getRepository(AccountCategories::class)->getCategoryList($user),
      ]);
    }
  
  /**
   * Saves form data (both insert / update)
   * @param Request $request
   * @param ManagerRegistry $registry
   * @param LoggerInterface $logger
   * @return Response
   * @throws \Exception
   */
  #[Route('/kontomanager/form-speichern',name: 'km_accFormSave',methods: ['POST'])]
  public function SaveForm(Request $request,ManagerRegistry $registry,LoggerInterface $logger):Response
    {
    /** @var User $user */
    $user = $this->getUser();
    $id   = (int)$request->get('accid');
    $obj  = $registry->getRepository(AccountData::class)->findOneBy(['RefUser' => $user,'id' => $id]);
    if($obj === null)
      {
      $obj = new AccountData();
      $obj->setRefUser($user);
      $lmsg = "Adding new accountData entry";
      $info = "Neuen Eintrag erzeugt";
      }
    else
      {
      $lmsg = "Editing existing accountData entry #$id";
      $info = "Eintrag #$id aktualisiert";
      }
    $amount = str_replace(",",".",$request->get('amount'));
    $obj->setBookingDate(new DateTimeImmutable($request->get('booking_date')))
      ->setAccountingNumber($request->get('account_number'))
      ->setRefCategory($registry->getRepository(AccountCategories::class)->find((int)$request->get('catid')))
      ->setAmount($amount)
      ->setCurrency($request->get('currency'))
      ->setDescription($request->get('description'))
      ->setIsIncomeByAmount($amount)
      ;
    $registry->getManager()->persist($obj);
    $registry->getManager()->flush();
    $logger->info($lmsg);
    $this->addFlash('info',$info);
    return $this->redirectToRoute('km_list');
    }
  
  /**
   * Removes one or multiple entries from list (via checkboxes)
   * @param Request $request
   * @param ManagerRegistry $registry
   * @return Response
   */
  #[Route('/kontomanager/liste-loeschen',name: 'km_accListDel',methods: ["POST"])]
  public function RemoveEntries(Request $request,ManagerRegistry $registry):Response
    {
    $removed = 0;
    /** @var User $user */
    $user = $this->getUser();
    foreach($request->get('ACC_REMOVAL') as $item)
      {
      $obj = $registry->getRepository(AccountData::class)->findOneBy(['RefUser' => $user,'id' => (int)$item]);
      if($obj === null)
        {
        continue;
        }
      $registry->getManager()->remove($obj);
      $removed++;
      }
    $registry->getManager()->flush();
    $this->addFlash('info',"Es wurden $removed Zeile(n) gelöscht");
    return $this->redirectToRoute("km_list");
    }
  
  }
