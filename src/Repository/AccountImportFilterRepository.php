<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\AccountImportFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
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

  }
