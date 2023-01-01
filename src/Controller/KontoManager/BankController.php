<?php declare(strict_types=1);
/**
 * Bank-related methods.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 0.1 (26-Feb-2022)
 */

namespace App\Controller\KontoManager;

use App\Entity\AccountBankAccounts;
use App\Entity\AccountData;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_USER")]
class BankController extends AbstractController
  {
  const ACT_NAV = 'bank';
  
  private ManagerRegistry $registry;
  private LoggerInterface $logger;
  
  /**
   * @param ManagerRegistry $registry
   * @param LoggerInterface $logger
   */
  public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
    $this->registry = $registry;
    $this->logger = $logger;
    }
  
  /**
   * Shows overview of all registered banking accounts.
   * @return Response
   */
  #[Route('kontomanager/bankkonten',name: 'km_bank')]
  public function overview():Response
    {
    /** @var User $user */
    $user = $this->getUser();
    return $this->render('kontomanager/bank_list.html.twig', [
      'ACTNAV'        => self::ACT_NAV,
      'BANKACCOUNTS'  => $this->registry->getRepository(AccountBankAccounts::class)->findBy(['RefUser' => $user]),
      ]);
    }
  
  /**
   * HTML Form to add/edit banking account data.
   * @param int $id
   * @return Response
   */
  #[Route('kontomanager/bankkonten/form/{id}',name: 'km_bank_form')]
  public function form(int $id = 0):Response
    {
    if($id)
      {
      $data = $this->LoadData($id);
      if($data === null)
        {
        $this->addFlash('error',"Bankkonto mit ID=$id nicht gefunden?!");
        $this->logger->error("form(): No bank account found with ID=$id");
        return $this->redirectToRoute('km_bank');
        }
      }
    else
      {
      $data = new AccountBankAccounts();
      }
    return $this->render('kontomanager/bank_form.html.twig', [
      'ACTNAV'  => self::ACT_NAV,
      'ENTRY'   => $data,
      ]);
    }
  
  /**
   * Saves form data
   * @param Request $request
   * @return Response
   */
  #[Route("kontomanager/bankkonten/speichern",name: "km_bank_save",methods: ['POST'])]
  public function save(Request $request):Response
    {
    $id = (int)$request->get('BANKID',0);
    if($id)
      {
      $data = $this->LoadData($id);
      if($data === null)
        {
        $this->addFlash('error',"Bankkonto mit ID=$id nicht gefunden - Speichern nicht möglich?!");
        $this->logger->error("save(): No bank account found with ID=$id");
        return $this->redirectToRoute('km_bank');
        }
      $logmsg = "Updated bank account #$id";
      }
    else
      {
      $data = new AccountBankAccounts();
      $data->setRefUser($this->getUser());
      $logmsg = "Added new bank account";
      }
    // @todo Check parameter first for validness?
    $data->setBankName($request->get('bankname'))
      ->setBankShortcut($request->get('shortcut'))
      ->setIban($request->get('iban'))
      ->setLogoName($request->get('logoname'))
      ;
    $this->registry->getManager()->persist($data);
    $this->registry->getManager()->flush();
    $this->addFlash('success',"Speicherung erfolgreich");
    $this->logger->info($logmsg);
    return $this->redirectToRoute('km_bank');
    }
  
  /**
   * Removes entry from database.
   * @param int $id
   * @return Response
   */
  #[Route("kontomanager/bankkonten/loeschen/{id}",name: "km_bank_del")]
  public function delete(int $id):Response
    {
    $data = $this->LoadData($id);
    if($data === null)
      {
      $this->addFlash('error',"Bankkonto mit ID=$id nicht gefunden - Löschen nicht möglich?!");
      $this->logger->error("delete(): No bank account found with ID=$id");
      return $this->redirectToRoute('km_bank');
      }
    $this->registry->getRepository(AccountData::class)->DeleteByBankId($data->getId(),$this->getUser()->getId());
    $this->registry->getManager()->remove($data);
    $this->registry->getManager()->flush();
    $this->addFlash('success',"Bankkonto wurde erfolgreich gelöscht");
    $this->logger->info("Removed bank account #$id");
    return $this->redirectToRoute('km_bank');
    }
  
  /**
   * Loads entry with $id for current user.
   * @param int $id
   * @return AccountBankAccounts|null
   */
  private function LoadData(int $id):?AccountBankAccounts
    {
    /** @var User $user */
    $user = $this->getUser();
    return $this->registry->getRepository(AccountBankAccounts::class)->findOneBy(['RefUser' => $user,'id' => $id]);
    }

  }
