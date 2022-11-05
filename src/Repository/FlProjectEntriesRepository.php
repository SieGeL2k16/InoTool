<?php

namespace App\Repository;

use App\Entity\FlProjectEntries;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FlProjectEntries>
 *
 * @method FlProjectEntries|null find($id, $lockMode = null, $lockVersion = null)
 * @method FlProjectEntries|null findOneBy(array $criteria, array $orderBy = null)
 * @method FlProjectEntries[]    findAll()
 * @method FlProjectEntries[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FlProjectEntriesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FlProjectEntries::class);
    }

    public function save(FlProjectEntries $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FlProjectEntries $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return FlProjectEntries[] Returns an array of FlProjectEntries objects
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

//    public function findOneBySomeField($value): ?FlProjectEntries
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
