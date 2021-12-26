<?php

namespace App\Repository;

use App\Entity\AccountImportFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AccountImportFilter|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountImportFilter|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountImportFilter[]    findAll()
 * @method AccountImportFilter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountImportFilterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountImportFilter::class);
    }

    // /**
    //  * @return AccountImportFilter[] Returns an array of AccountImportFilter objects
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
    public function findOneBySomeField($value): ?AccountImportFilter
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
