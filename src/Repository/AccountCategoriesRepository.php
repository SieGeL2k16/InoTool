<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\AccountCategories;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AccountCategories|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountCategories|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountCategories[]    findAll()
 * @method AccountCategories[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountCategoriesRepository extends ServiceEntityRepository
  {
  /** @var Connection $db */
  private Connection $db;
  
  public function __construct(ManagerRegistry $registry)
    {
    parent::__construct($registry, AccountCategories::class);
    $this->db = $this->getEntityManager()->getConnection();
    }
  
  /**
   * Returns a list of categories with special category id = 0 on Top of list
   * @param User $user
   * @return array
   * @throws Exception
   */
  public function getCategoryList(User $user):array
    {
    $ret = [['id' => 0, 'name' => 'Keine Kategorie']];
    $stmt = $this->db->executeQuery("select id,name from account_categories where id != 0 and ref_user_id=:uid order by name",['uid' => $user->getId()]);
    while($d = $stmt->fetchAssociative())
      {
      $ret[] = $d;
      }
    return $ret;
    }
  
  /**
   * Returns complete list of all user-defined categories together with number of assignments in accounting_data table.
   * @param User $user
   * @return array
   * @throws Exception
   */
  public function GetFullCategoryList(User $user):array
    {
    $ret  = [];
    $stmt = $this->db->executeQuery("select ac.*,(select count(*) from account_data where ref_category_id=ac.id) as data_count from account_categories ac where ac.ref_user_id=:uid order by ac.name",['uid' => $user->getId()]);
    while($d = $stmt->fetchAssociative())
      {
      $ret[] = $d;
      }
    return $ret;
    }
  
  /**
   * Removes category from database, reassigns existing entries to null.
   * @param User $user
   * @param int $catid
   * @return void
   * @throws Exception
   */
  public function Delete(User $user, int $catid): void
  {
    $this->db->executeQuery("update account_data set ref_category_id = null where ref_user_id=:uid and ref_category_id=:cid",['uid' => $user->getId(),'cid' => $catid]);
    $this->db->executeQuery("delete from account_categories where ref_user_id=:uid and id=:cid",['uid' => $user->getId(),'cid' => $catid]);
    }
  
  }
