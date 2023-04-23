<?php

namespace App\Repository;

use App\Entity\FlServiceContractEntries;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FlServiceContractEntries>
 *
 * @method FlServiceContractEntries|null find($id, $lockMode = null, $lockVersion = null)
 * @method FlServiceContractEntries|null findOneBy(array $criteria, array $orderBy = null)
 * @method FlServiceContractEntries[]    findAll()
 * @method FlServiceContractEntries[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FlServiceContractEntriesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FlServiceContractEntries::class);
    }

    public function save(FlServiceContractEntries $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FlServiceContractEntries $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return FlServiceContractEntries[] Returns an array of FlServiceContractEntries objects
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

//    public function findOneBySomeField($value): ?FlServiceContractEntries
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
