<?php

namespace App\Repository;

use App\Entity\AccountCategories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AccountCategories|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountCategories|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountCategories[]    findAll()
 * @method AccountCategories[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountCategoriesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountCategories::class);
    }

    // /**
    //  * @return AccountCategories[] Returns an array of AccountCategories objects
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
    public function findOneBySomeField($value): ?AccountCategories
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
