<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\AccountBankAccounts;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AccountBankAccounts|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountBankAccounts|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountBankAccounts[]    findAll()
 * @method AccountBankAccounts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountBankAccountsRepository extends ServiceEntityRepository
  {
  public function __construct(ManagerRegistry $registry)
    {
    parent::__construct($registry, AccountBankAccounts::class);
    }
  
  /**
   * Returns latest balance found for in all bank accounts
   * @param User $user
   * @return array
   * @throws Exception
   */
  public function getCurrentBalance(User $user):array
    {
    $stmt = $this->getEntityManager()->getConnection()->executeQuery("select aba.balance, aba.balance_date from account_bank_accounts aba where aba.ref_user_id=? and aba.balance_date = (select max(i.balance_date) from account_bank_accounts i where i.id = aba.id)",[$user->getId()]);
    $res = $stmt->fetchAssociative();
    if($res === false)
      {
      return [];
      }
    return $res;
    }
  
  }
