<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\FlProjectEntries;
use App\Entity\User;
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
      select fpe.*,fp.project_name,c.name as customer_name
        from fl_project_entries fpe,fl_projects fp,fl_customer c
       where to_char(entry_date,'YYYY-MM-DD') = :ymd
         and fpe.ref_project_id = fp.id
         and fpe.ref_user_id = :uid
         and fp.ref_customer_id = c.id
       order by id",[
        'ymd' => $date,
        'uid' => $user->getId(),
      ]);
    return $stmt->fetchAllAssociative();
    }
  
  /**
   * Returns last <n> entries for the given user.
   * @param UserInterface $user
   * @param int $nr_entries
   * @return array
   * @throws Exception
   */
  public function getLastEntries(UserInterface $user, int $nr_entries = 5):array
    {
    $stmt = $this->getEntityManager()->getConnection()->executeQuery("
      select fpe.*,fp.project_name,c.name as customer_name
        from fl_project_entries fpe,fl_projects fp,fl_customer c
       where fpe.ref_user_id = :uid
         and fpe.ref_project_id = fp.id
         and fp.ref_customer_id = c.id
       order by fpe.entry_date desc
      limit :ln",[
      'uid' => $user->getId(),
      'ln'  => $nr_entries,
      ]);
    return $stmt->fetchAllAssociative();
    }
  
  /**
   * Returns yearly profit for the given user.
   * @param User|UserInterface $user
   * @return array
   * @throws Exception
   */
  public function getYearlyProfit(User|UserInterface $user):array
    {
    $stmt = $this->getEntityManager()->getConnection()->executeQuery("
        select sum(i.salary), sum(i.work_time_in_secs) as sum_worktime,i.y
          from (select calculateProjectEntry(fpe.WORK_TIME_IN_SECS,p.WORK_UNIT,p.PAY_PER_WORK_UNIT) as salary,
                       to_char(fpe.entry_date, 'YYYY') as y,
                       fpe.work_time_in_secs
                  from fl_project_entries fpe,fl_projects p
                 where fpe.ref_user_id = :uid
                   and fpe.ref_project_id = p.id
                   and p.no_reporting = false
                union all
		            select case when inv.no_tax = false then inv.SUM_BRUTTO else inv.SUM_NETTO end AS salary,
                       to_char(inv.invoice_date , 'YYYY') as y,
                       0
                  FROM fl_invoices inv
                 WHERE inv.ref_user_id  = :uid
                   AND inv.invoice_type = 0
               ) i
        group by i.y
        order by 2",['uid' => $user->getId()]);
    return $stmt->fetchAllAssociative();
    }
  
  /**
   * Returns summary of monthly profit for a given year.
   * @param User|UserInterface $user
   * @param int|NULL $year Year to load, NULL for current year
   * @return array
   * @throws Exception
   */
  public function getYearProfitByMonth(User|UserInterface $user, int $year = null):array
    {
    if($year === null)
      {
      $year = date('Y');
      }
    $stmt = $this->getEntityManager()->getConnection()->executeQuery("
        select sum(i.salary),i.m
          from (
                select calculateProjectEntry(fpe.WORK_TIME_IN_SECS,p.WORK_UNIT,p.PAY_PER_WORK_UNIT) as salary,
                       to_char(fpe.entry_date, 'MM') as m
                  from fl_project_entries fpe,fl_projects p
                 where fpe.ref_user_id = :uid
                   and to_char(fpe.entry_date,'YYYY') = :y
                   and fpe.ref_project_id = p.id
                   and p.no_reporting = false
                union all
		            select case when inv.no_tax = false then inv.SUM_BRUTTO else inv.SUM_NETTO end AS salary,
                       to_char(inv.invoice_date , 'MM') as m
                  FROM fl_invoices inv
                 WHERE inv.ref_user_id  = :uid
                   AND inv.invoice_type = 0
                   AND TO_CHAR(inv.INVOICE_DATE,'YYYY') = :y
               ) i
        group by i.m
        order by 2",['uid' => $user->getId(),'y' => $year]);
      return $stmt->fetchAllAssociative();
    }
  
  /**
   * Returns project entries for the given user and year+month combo.
   * @param User|UserInterface $user
   * @param string $yyyymm
   * @return array[]
   * @throws Exception
   */
  public function getEntriesFromYYYYMM(User|UserInterface $user, string $yyyymm = ""):array
    {
    if($yyyymm === "")
      {
      $yyyymm = date('Ym');
      }
    $stmt = $this->getEntityManager()->getConnection()->executeQuery("
      select pe.id,
             calculateProjectEntry(pe.WORK_TIME_IN_SECS,p.WORK_UNIT,p.PAY_PER_WORK_UNIT) as salary,
             to_char(pe.entry_date, 'YYYY-MM-DD') as ymd,
             p.project_name,c.name as customer_name,
             p.no_reporting,
             pe.work_time_in_secs
        from fl_project_entries pe, fl_projects p, fl_customer c
       where p.id = pe.ref_project_id
         and pe.ref_user_id=:uid
         and c.ref_user_id=:uid
         and c.id = p.ref_customer_id
         and to_char(pe.entry_date,'YYYYMM') = :ym
       order by 3 desc,1 desc
      ",['uid' => $user->getId(),'ym' => $yyyymm]);
    return $stmt->fetchAllAssociative();
    }
  
  }
