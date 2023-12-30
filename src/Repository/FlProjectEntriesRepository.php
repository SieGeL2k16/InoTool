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
      select fpe.*,fp.project_name,c.name as customer_name,coalesce(calculateProjectEntry(fpe.WORK_TIME_IN_SECS,fp.WORK_UNIT,fp.PAY_PER_WORK_UNIT),costs) as salary
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
          from (select coalesce(calculateProjectEntry(fpe.WORK_TIME_IN_SECS,p.WORK_UNIT,p.PAY_PER_WORK_UNIT),fpe.costs) as salary,
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
                union all
                select sco.billing_fee as salary,
                       to_char(sco.billing_date , 'YYYY') as y,
                       0
                  from fl_service_contract_entries sco
                 where sco.ref_user_id = :uid
               ) i
        group by i.y
        order by 3",['uid' => $user->getId()]);
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
                select coalesce(calculateProjectEntry(fpe.WORK_TIME_IN_SECS,p.WORK_UNIT,p.PAY_PER_WORK_UNIT),fpe.costs) as salary,
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
                union all
                select sco.billing_fee,
                       to_char(sco.billing_date , 'MM') as m
                  from fl_service_contract_entries sco
                 where sco.ref_user_id = :uid
                   and to_char(sco.billing_date,'YYYY') = :y
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
             coalesce(calculateProjectEntry(pe.WORK_TIME_IN_SECS,p.WORK_UNIT,p.PAY_PER_WORK_UNIT),pe.costs) as salary,
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
  
  /**
   * Returns project entries for a given project id and date range.
   * @param User|UserInterface $user
   * @param int $pid
   * @param DateTime $sd
   * @param DateTime $ed
   * @return array
   * @throws Exception
   */
  public function getEntriesForProjectAndRange(User|UserInterface $user, int $pid, DateTime $sd, DateTime $ed):array
    {
    $stmt = $this->getEntityManager()->getConnection()->executeQuery("
      select pe.id,
             coalesce(calculateProjectEntry(pe.WORK_TIME_IN_SECS,p.WORK_UNIT,p.PAY_PER_WORK_UNIT),pe.costs) as salary,
             to_char(pe.entry_date, 'YYYY-MM-DD') as ymd,
             p.project_name,
             c.name as customer_name,
             p.no_reporting,
             pe.work_time_in_secs,
             pe.work_description,
             pe.record_type
        from fl_project_entries pe, fl_projects p, fl_customer c
       where p.id = pe.ref_project_id
         and pe.ref_user_id=:uid
         and c.ref_user_id=:uid
         and p.id = :pid
         and c.id = p.ref_customer_id
         and pe.entry_date between :sd and :ed
       order by 3 desc,1 desc
      ",['uid' => $user->getId(),'pid' => $pid,'sd' => $sd->format('Y-m-d').' 00:00:00','ed' => $ed->format('Y-m-d').' 23:59:59']);
    $data = $stmt->fetchAllAssociative();
    $totals = ['ROWS' => 0, 'WORK_TIME' => 0, 'SALARY' => 0.00, 'PROJECT' => '', 'CUSTOMER' => ''];
    foreach($data as $d)
      {
      if($totals['PROJECT'] === "")
        {
        $totals['PROJECT']      = $d['project_name'];
        $totals['CUSTOMER']     = $d['customer_name'];
        $totals['NO_REPORTING'] = false;
        }
      $totals['ROWS']++;
      $totals['WORK_TIME']+=(int)$d['work_time_in_secs'];
      $totals['SALARY']+=(floatval($d['salary']));
      if($d['no_reporting'] === true)
        {
        $totals['NO_REPORTING'] = true;
        }
      }
    return ['data' => $data,'totals' => $totals];
    }
  
  }
