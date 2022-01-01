<?php

namespace App\Repository;

use App\Entity\AccountBankAccounts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    // /**
    //  * @return AccountBankAccounts[] Returns an array of AccountBankAccounts objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AccountBankAccounts
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
