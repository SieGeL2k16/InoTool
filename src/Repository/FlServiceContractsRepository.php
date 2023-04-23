<?php

namespace App\Repository;

use App\Entity\FlServiceContracts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FlServiceContracts>
 *
 * @method FlServiceContracts|null find($id, $lockMode = null, $lockVersion = null)
 * @method FlServiceContracts|null findOneBy(array $criteria, array $orderBy = null)
 * @method FlServiceContracts[]    findAll()
 * @method FlServiceContracts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FlServiceContractsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FlServiceContracts::class);
    }

    public function save(FlServiceContracts $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FlServiceContracts $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return FlServiceContracts[] Returns an array of FlServiceContracts objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FlServiceContracts
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
