<?php

namespace App\Repository;

use App\Entity\FlInvoices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FlInvoices>
 *
 * @method FlInvoices|null find($id, $lockMode = null, $lockVersion = null)
 * @method FlInvoices|null findOneBy(array $criteria, array $orderBy = null)
 * @method FlInvoices[]    findAll()
 * @method FlInvoices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FlInvoicesRepository extends ServiceEntityRepository
  {
  /** @var Connection $db  */
  private Connection $db;
  
  public function __construct(ManagerRegistry $registry)
    {
    parent::__construct($registry, FlInvoices::class);
    $this->db = $this->getEntityManager()->getConnection();
    }

  public function save(FlInvoices $entity, bool $flush = false): void
    {
    $this->getEntityManager()->persist($entity);
    if ($flush) {
      $this->getEntityManager()->flush();
      }
    }

  public function remove(FlInvoices $entity, bool $flush = false): void
    {
    $this->getEntityManager()->remove($entity);
    if ($flush) {
      $this->getEntityManager()->flush();
      }
    }
  
  /**
   * Returns totals for invoices (total_count,total_sum,entries_year)
   * @param int $uid
   * @param string|NULL $year
   * @return array total_count,total_sum,entries_year
   * @throws Exception
   */
  public function getTotals(int $uid, string $year = null):array
    {
    if($year === null)
      {
      $year = date('Y');
      }
    $SQL  = "
        SELECT
        (SELECT COUNT(*) FROM fl_invoices fi WHERE fi.ref_user_id =:uid) AS total_count,
        (SELECT SUM(case when fi2.sum_brutto is null then fi2.sum_netto else fi2.sum_brutto end) FROM fl_invoices fi2 WHERE fi2.ref_user_id =:uid) AS total_sum,
        (SELECT COUNT(*) FROM fl_invoices fi3 WHERE fi3.ref_user_id =:uid AND TO_CHAR(fi3.invoice_date ,'YYYY') = :y) AS entries_year
        ";
    $stmt = $this->db->executeQuery($SQL,['uid' => $uid, 'y' => $year]);
    return $stmt->fetchAssociative();
    }
  
  /**
   * Called from AJAX to fetch data for datatables.
   * @param array $params
   * @param int $userid
   * @return array
   * @throws Exception
   */
  public function getDataTablesValues(array $params, int $userid):array
    {
    $SEARCH_SQL = "";
    $par = ['uid' => $userid];
    if($params['SEARCH'] !== "")
      {
      $SEARCH_SQL.= " AND (i.invoice_number LIKE :srch OR lower(i.comment) LIKE :srch OR lower(i.pdf_filename) LIKE :srch or i.invoice_date::text like :srch)";
      $par['srch'] = '%'.mb_strtolower($params['SEARCH']).'%';
      }
    if($params['F_TYPE'] !== "")
      {
      $SEARCH_SQL.=" and i.invoice_type = :it";
      $par['it'] = $params['F_TYPE'];
      }
    if($params['F_PAID'] !== "")
      {
      $SEARCH_SQL.= " and i.is_paid = :ip";
      if($params['F_PAID'] === "0")
        {
        $ip = "false";
        }
      else
        {
        $ip = "true";
        }
      $par['ip'] = $ip;
      }
    $SQL  = "SELECT i.id,i.invoice_number,i.invoice_date,c.name as customer_name,i.invoice_type,
                    case when i.sum_brutto is null then i.sum_netto else i.sum_brutto end as invoice_fee,
                    i.is_paid,i.pdf_filename
               FROM fl_invoices i,fl_customer c
              WHERE i.ref_user_id=:uid
                and i.ref_customer_id =c.id
              $SEARCH_SQL
              ORDER BY {$params['ORDER']}, i.id desc
              LIMIT {$params['LIMIT']} OFFSET {$params['START']}";
    $data   = $this->db->executeQuery($SQL,$par)->fetchAllNumeric();
    $total  = $this->db->executeQuery("select count(*) as cnt from fl_invoices i,fl_customer c WHERE i.ref_user_id=:uid and i.ref_customer_id =c.id $SEARCH_SQL",$par)->fetchOne();
    return [
      'DATA'  => $data,
      'TOTAL' => $total,
      ];
    }
  }
