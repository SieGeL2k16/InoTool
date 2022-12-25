<?php

namespace App\Repository;

use App\Entity\AppConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method AppConfig|null find($id, $lockMode = null, $lockVersion = null)
 * @method AppConfig|null findOneBy(array $criteria, array $orderBy = null)
 * @method AppConfig[]    findAll()
 * @method AppConfig[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppConfigRepository extends ServiceEntityRepository
  {
  public function __construct(ManagerRegistry $registry)
    {
    parent::__construct($registry, AppConfig::class);
    }
  
  /**
   * Returns list of elements for the given
   * @param array $keys
   * @param UserInterface|null $user
   * @return float|int|mixed|string
   */
  public function getListByKeys(array $keys,?UserInterface $user)
    {
    $qb = $this->createQueryBuilder('c')
            ->andWhere('c.KeyName in (:val)')
            ->setParameter('val', $keys);
    if($user !== null)
      {
      $qb->andWhere('c.RefUser = :uid')->setParameter('uid',$user);
      }
    $qb->orderBy('c.KeyName', 'ASC');
    $query = $qb->getQuery();
    return $query->getResult();
    }
  
    // /**
    //  * @return AppConfig[] Returns an array of AppConfig objects
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
    public function findOneBySomeField($value): ?AppConfig
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
