<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\AccountImportFilter;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AccountImportFilter|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountImportFilter|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountImportFilter[]    findAll()
 * @method AccountImportFilter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountImportFilterRepository extends ServiceEntityRepository
  {
  /** @var Connection */
  private Connection $db;
  
  public function __construct(ManagerRegistry $registry)
    {
    parent::__construct($registry, AccountImportFilter::class);
    $this->db = $this->getEntityManager()->getConnection();
    }
  
  /**
   * Removes an entry referenced by userid and entry id.
   * @param User $user
   * @param int $ifid
   * @return void
   * @throws Exception
   */
  public function Delete(User $user, int $ifid): void
    {
    $this->db->executeQuery("delete from account_import_filter where ref_user_id=:uid and id=:ifid",['uid' => $user->getId(), 'ifid' => $ifid]);
    }
  }
