<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\FlCustomer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<FlCustomer>
 *
 * @method FlCustomer|null find($id, $lockMode = null, $lockVersion = null)
 * @method FlCustomer|null findOneBy(array $criteria, array $orderBy = null)
 * @method FlCustomer[]    findAll()
 * @method FlCustomer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FlCustomerRepository extends ServiceEntityRepository
  {
  public function __construct(ManagerRegistry $registry)
    {
    parent::__construct($registry, FlCustomer::class);
    }

  public function save(FlCustomer $entity, bool $flush = false): void
    {
     $this->getEntityManager()->persist($entity);
     if ($flush) {
      $this->getEntityManager()->flush();
      }
    }

  public function remove(FlCustomer $entity, bool $flush = false): void
    {
     $this->getEntityManager()->remove($entity);
     if ($flush) {
      $this->getEntityManager()->flush();
      }
    }
  
  /**
   * Returns customer data to be feed to datatables.
   * @param array $params Passed parameters from DT
   * @param int $userid UserID to check for
   * @param int $columncount Number of columns fetched from DB (for total count)
   * @return array
   * @throws Exception
   */
  public function GetDataTablesValues(array $params, int $userid,int $columncount):array
    {
    $SEARCH_SQL = "";
    $par = ['uid' => $userid];
    if($params['SEARCH'] !== "")
      {
      $SEARCH_SQL.= " AND (c.customer_number LIKE :srch OR lower(c.name) LIKE :srch OR lower(c.contact_name) LIKE :srch or lower(c.contact_email) like :srch)";
      $par['srch'] = '%'.mb_strtolower($params['SEARCH']).'%';
      }
    if($params['F_ACTIVE'] !== "")
      {
      $SEARCH_SQL.=" AND c.active=:act";
      $par['act'] = ($params['F_ACTIVE'] === '0') ? 'false' : 'true';
      }
    $SQL  = "SELECT c.id,c.customer_number,c.name,c.contact_name,c.contact_email,c.phone_business,
                    case when c.active = true then '<i class=\"fa-solid fa-check text-success\"></i>' else '<i class=\"fa-solid fa-xmark text-danger\"></i>' end as active,
                    count(*) OVER() AS total_count
               FROM fl_customer c
               WHERE c.ref_user_id=:uid $SEARCH_SQL
              ORDER BY {$params['ORDER']}, c.id desc
              LIMIT {$params['LIMIT']} OFFSET {$params['START']}";
    $data   = $this->getEntityManager()->getConnection()->executeQuery($SQL,$par)->fetchAllNumeric();
    $total = 0;
    if(count($data))
      {
      $total  = $data[0][$columncount];      // Total Count column
      }
    return [
      'DATA'  => $data,
      'TOTAL' => $total,
      ];
    }
  
  /**
   * Returns the highest customer number.
   * NOTE: Use this number only as suggestion!
   * @param UserInterface $user
   * @return string
   * @throws Exception
   */
  public function GetNextCustomerNumber(UserInterface $user):string
    {
    $stmt = $this->getEntityManager()->getConnection()->executeQuery("select to_char(max(regexp_replace(customer_number,'\D','')::int)+1,'0000009') as maxcustid from fl_customer fc where ref_user_id=:uid",['uid' => $user->getId()]);
    return $stmt->fetchAssociative()['maxcustid'];
    }
  }
