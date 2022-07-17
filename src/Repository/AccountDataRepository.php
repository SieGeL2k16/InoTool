<?php

namespace App\Repository;

use App\Entity\AccountCategories;
use App\Entity\AccountData;
use App\Entity\AccountImportFilter;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
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
  /** @var Statement $istmt Insert Statement */
  private Statement $istmt;
  
  public function __construct(ManagerRegistry $registry)
    {
    parent::__construct($registry, AccountData::class);
    $this->istmt = $this->getEntityManager()->getConnection()->prepare("insert into account_data(id, ref_category_id, ref_user_id, booking_date, description, amount, accounting_number, currency, bank_id, is_income) values(nextval('account_data_id_seq'),:catid,:uid,:bdate,:descr,:amount,:accnum,:currency,:bankid,:isin)");
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
    if($params['F_CATEGORY'] !== null && (int)$params['F_CATEGORY'] !== -1 && (int)$params['F_CATEGORY'] !== 0)
      {
      $where.=" and ac.id=:cid";
      $dbpar['cid'] = $params['F_CATEGORY'];
      }
    if($params['F_CATEGORY'] === "0")
      {
      $where.= " and ad.ref_category_id is null";
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
    $SQL  = "select ad.id, to_char(ad.booking_date,'DD.MM.YYYY') AS dt,ac.id as catid,ac.name as category_name,ad.description,ad.amount,aba.bank_shortcut,aba.logo_name,ad.is_income,count(*) OVER() AS total_count
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
  
  /**
   * Updates entry with new category id.
   * @param User $user
   * @param int $accid
   * @param int $catid
   * @return void
   * @throws Exception
   */
  public function CatSaver(User $user, int $accid, int $catid)
    {
    $par  = ['catid' => ($catid === 0) ? null : $catid, 'uid' => $user->getId(), 'accid' => $accid];
    $SQL  = "update account_data set ref_category_id=:catid where ref_user_id=:uid and id=:accid";
    $stmt = $this->getEntityManager()->getConnection()->executeQuery($SQL,$par);
    }
  
  /**
   * Inserts new record to account_data, ignoring duplicate values
   * @param array $data
   * @return int
   * @throws Exception
   */
  public function Insert(array $data):int
    {
    try
      {
      $this->istmt->executeStatement($data);
      return $this->getEntityManager()->getConnection()->lastInsertId();
      }
    catch(UniqueConstraintViolationException $e)
      {
      return 0;
      }
    }
  
  /**
   * Removes all rows with given bankid + userid
   * @param int $bankid
   * @param int $userid
   * @return Result
   * @throws Exception
   */
  public function DeleteByBankId(int $bankid, int $userid):Result
    {
    return $this->getEntityManager()->getConnection()->executeQuery("delete from account_data where bank_id=:bid and ref_user_id=:uid",['bid' => $bankid, 'uid' => $userid]);
    }
  
  /**
   * Returns list of all existing years (for reporting start screen)
   * @return array
   * @throws Exception
   */
  public function GetDistinctYears():array
    {
    $res = $this->getEntityManager()->getConnection()->executeQuery("select distinct to_char(ad.booking_date,'YYYY') as y from account_data ad order by 1 desc");
    return $res->fetchAllAssociative();
    }
  }
