<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\FlProjectEntries;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

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
  const CACHE_EXPIRES = 600;
  
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
  
  /**
   * @param UserInterface $user
   * @param DateTime $start
   * @param DateTime $end
   * @return array[]
   * @throws Exception
   */
  public function getEventListByDateRange(UserInterface $user, DateTime $start, DateTime $end): array
    {
    $stmt = $this->getEntityManager()->getConnection()->executeQuery("select to_char(fpe.entry_date,'YYYY-MM-DD') as edate,count(*) as cnt from fl_project_entries fpe where fpe.entry_date between :sd and :ed and fpe.ref_user_id=:uid group by 1 order by 1",[
      'sd'  => $start->format('Y-m-d').' 00:00:00',
      'ed'  => $end->format('Y-m-d').' 23:59:59',
      'uid' => $user->getId(),
      ]);
    return $stmt->fetchAllAssociative();
    }
  
  /**
   * Returns list of entries for a given date in format YYYY-MM-DD
   * @param UserInterface $user
   * @param string $date
   * @return array[]
   * @throws Exception
   */
  public function getEntriesForDate(UserInterface $user,string $date): array
    {
    $stmt = $this->getEntityManager()->getConnection()->executeQuery("
      select fpe.*,fp.project_name
        from fl_project_entries fpe,fl_projects fp
       where to_char(entry_date,'YYYY-MM-DD') = :ymd
         and fpe.ref_project_id = fp.id
         and fpe.ref_user_id = :uid
       order by id",[
        'ymd' => $date,
        'uid' => $user->getId(),
      ]);
    return $stmt->fetchAllAssociative();
    }
  }
