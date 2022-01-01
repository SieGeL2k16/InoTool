<?php

namespace App\Repository;

use App\Entity\AccountCategories;
use App\Entity\AccountData;
use App\Entity\AccountImportFilter;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use JetBrains\PhpStorm\ArrayShape;
use NumberFormatter;

/**
 * @method AccountData|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountData|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountData[]    findAll()
 * @method AccountData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountData::class);
    }
  
  /**
   * Returns overview of a given (or current) year
   * @param int $uid
   * @param int $year
   * @return array
   * @throws Exception
   */
  #[ArrayShape(['INCOME' => "float", 'OUTCOME' => "float", 'DIFFERENCE' => "float"])]
  public function GetYearlyStats(int $uid, int $year = 0):array
    {
    $stats = ['INCOME' => 0.00, 'OUTCOME' => 0.00, 'DIFFERENCE' => 0.0];
    $p = ['uid' => $uid];
    $s = "";
    if($year !== 0)
      {
      $p['y']  = $year;
      $s = "and to_char(booking_date,'YYYY')=:y ";
      }
    $stmt = $this->getEntityManager()->getConnection()->executeQuery("select sum(amount) as asum,is_income from account_data where ref_user_id=:uid $s group by is_income",$p);
    while($d = $stmt->fetchAssociative())
      {
      if((int)$d['is_income'] === 0)
        {
        $stats['OUTCOME'] = $d['asum'];
        }
      else
        {
        $stats['INCOME'] = $d['asum'];
        }
      }
    $stats['DIFFERENCE'] = abs($stats['INCOME']) - abs($stats['OUTCOME']);
    return $stats;
    }
  
  /**
   * @param User $user
   * @return array
   * @throws Exception
   */
  public function GetDatabaseStatistics(User $user):array
    {
    $stmt = $this->getEntityManager()->getConnection()->executeQuery("select count(*) as totcnt,min(booking_date) as mind, max(booking_date) as maxd from account_data ad where ad.ref_user_id=:uid",['uid' => $user->getId()]);
    $data =  $stmt->fetchAssociative();
    $stats['TOTAL_ROWS']        = $data['totcnt'];
    $stats['MIN_DATE']          = $data['mind'];
    $stats['MAX_DATE']          = $data['maxd'];
    $stats['TOTAL_CATEGORIES']  = $this->getEntityManager()->getRepository(AccountCategories::class)->count(['RefUser' => $user]);
    $stats['TOTAL_FILTER']      = $this->getEntityManager()->getRepository(AccountImportFilter::class)->count(['RefUser' => $user]);
    return $stats;
    }
  
  /**
   * Calculates the sum of a given category grouped by YYYYMM
   * @param User $user
   * @param int $category
   * @return array
   * @throws Exception
   */
  public function GetCategorySumYYYYMM(User $user, int $category):array
    {
    $ret  = [];
    $stmt = $this->getEntityManager()->getConnection()->executeQuery("select sum(amount) as asum,to_char(booking_date,'YYYYMM') as yyyymm from account_data where ref_user_id=:uid and ref_category_id=:cid group by 2 order by 2",['uid' => $user->getId(),'cid' => $category]);
    while($d = $stmt->fetchAssociative())
      {
      $ret[$d['yyyymm']] = $d['asum'];
      }
    return $ret;
    }
  
  /**
   * Calculates the sum of a given category grouped by YYYY (year)
   * @param User $user
   * @param int $category
   * @return array
   * @throws Exception
   */
  public function GetCategorySumYYYY(User $user, int $category):array
    {
    $ret  = [];
    $stmt = $this->getEntityManager()->getConnection()->executeQuery("select sum(amount) as asum,to_char(booking_date,'YYYY') as yyyymm from account_data where ref_user_id=:uid and ref_category_id=:cid group by 2 order by 2",['uid' => $user->getId(),'cid' => $category]);
    while($d = $stmt->fetchAssociative())
      {
      $ret[$d['yyyymm']] = $d['asum'];
      }
    return $ret;
    }
  
  /**
   * Datatables Ajax backend for account_data table.
   * @param int $uid
   * @param array $params
   * @return array
   * @throws Exception
   */
  public function GetDataTablesValues(int $uid,array $params):array
    {
    $SEARCH_SQL = "";
    $par = []; //['sd' => $params['SD'], 'ed' => $params['ED']];
    if($params['SEARCH'] !== "")
      {
      $SEARCH_SQL   = " WHERE (lower(ad.description) LIKE :srch OR lower(l.level_name) LIKE :srch OR lower(l.message) LIKE :srch)";
      $par['srch']  = '%'.mb_strtolower($params['SEARCH']).'%';
      }
      //               WHERE l.created_at BETWEEN :sd AND :ed
    $SQL  = "SELECT ad.id, to_char(ad.booking_date,'DD.MM.YYYY') AS dt,ac.name,ad.description,ad.amount,ad.bank_id, count(*) OVER() AS total_count
               FROM account_data ad
               left join account_categories ac on (ad.ref_category_id = ac.id)
                $SEARCH_SQL
              ORDER BY {$params['ORDER']} {$params['SDIR']}
              LIMIT {$params['LIMIT']} OFFSET {$params['START']}";
    $data   = []; //$this->getEntityManager()->getConnection()->executeQuery($SQL,$par)->fetchAllNumeric();
    $total = 0;
    $stmt = $this->getEntityManager()->getConnection()->executeQuery($SQL,$par);
    while($item = $stmt->fetchAssociative())
      {
      $data[] = [
        $item['id'],
        $item['dt'],
        $item['name'],
        $item['description'],
        (new NumberFormatter("de-DE", NumberFormatter::CURRENCY))->format($item['amount']),
        $item['bank_id'],
        $item['total_count']
      ];
      }
    if(count($data))
      {
      $total  = $data[0][6];      // Total Count column
      }
    return [
      'DATA'  => $data,
      'TOTAL' => $total,
      ];
    }
  
  /**
   * Returns data stream as choosen by given parameters.
   * @param User $user
   * @param array $params
   * @return array
   * @throws Exception
   */
  public function GetBrowserData(User $user,array $params):array
    {
    $dbpar = ['uid' => $user->getId()];
    $where  = "";
    if($params['F_CATEGORY'] !== null && (int)$params['F_CATEGORY'] !== -1)
      {
      $where.=" and ac.id=:cid";
      $dbpar['cid'] = $params['F_CATEGORY'];
      }
    if($params['F_MONTH'] !== null && (int)$params['F_MONTH'] !== -1)
      {
      $where.=" and to_char(ad.booking_date,'MM')=:mon";
      $dbpar['mon'] = sprintf("%02d",$params['F_MONTH']);
      }
    if($params['F_YEAR'] !== null && (int)$params['F_YEAR'] !== -1)
      {
      $where.=" and to_char(ad.booking_date,'YYYY')=:y";
      $dbpar['y'] = $params['F_YEAR'];
      }
    $SQL  = "select ad.id, to_char(ad.booking_date,'DD.MM.YYYY') AS dt,ac.name as category_name,ad.description,ad.amount,aba.bank_shortcut,ad.is_income,count(*) OVER() AS total_count
             from account_data ad
             left join account_categories ac on (ad.ref_category_id = ac.id)
             left join account_bank_accounts aba on(ad.accounting_number = aba.iban)
             where ad.ref_user_id=:uid
             $where
             order by ad.booking_date desc
            ";
    $stmt = $this->getEntityManager()->getConnection()->executeQuery($SQL,$dbpar);
    return $stmt->fetchAllAssociative();
    }

  }
