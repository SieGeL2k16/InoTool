<?php

namespace App\Repository;

use App\Entity\FlProjects;
use App\Twig\AppRuntime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FlProjects>
 *
 * @method FlProjects|null find($id, $lockMode = null, $lockVersion = null)
 * @method FlProjects|null findOneBy(array $criteria, array $orderBy = null)
 * @method FlProjects[]    findAll()
 * @method FlProjects[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FlProjectsRepository extends ServiceEntityRepository
  {
  private AppRuntime $appRuntime;
  
  public function __construct(ManagerRegistry $registry, AppRuntime $appRuntime)
    {
    parent::__construct($registry, FlProjects::class);
    $this->appRuntime = $appRuntime;
    }

    public function save(FlProjects $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FlProjects $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
  
  /**
   * Returns projects data to be feed to datatables.
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
      $SEARCH_SQL.= " AND (lower(c.name) LIKE :srch OR lower(p.project_name) LIKE :srch)";
      $par['srch'] = '%'.mb_strtolower($params['SEARCH']).'%';
      }
    if($params['F_STATUS'] !== "")
      {
      $SEARCH_SQL.=" AND p.status=:st";
      $par['st'] = (int)$params['F_STATUS'];
      }
    if($params['F_REPORTING'] !== "")
      {
      if(mb_strtolower($params['F_REPORTING']) === 'true')
        {
        $par['nr'] = 'true';
        }
      else
        {
        $par['nr'] = 'false';
        }
      $SEARCH_SQL.=" AND p.no_reporting = :nr";
      }
    if($params['F_CUSTOMER'] !== "")
      {
      $SEARCH_SQL.=" and p.ref_customer_id = :cid";
      $par['cid'] = (int)$params['F_CUSTOMER'];
      }
    $SQL = "select p.id,p.project_name,c.name as customer_name,p.status,case when p.no_reporting = false then 0 else 1 end as no_reporting,p.pay_per_work_unit,p.currency,
                   count(*) OVER() AS total_count
              from fl_projects p, fl_customer c
             where p.ref_customer_id = c.id
               and c.ref_user_id = :uid
               and p.ref_user_id = :uid
               $SEARCH_SQL
              ORDER BY {$params['ORDER']}, p.id desc
              LIMIT {$params['LIMIT']} OFFSET {$params['START']}";
    $total = 0;
    //  $data   = $this->getEntityManager()->getConnection()->executeQuery($SQL,$par)->fetchAllNumeric();
    $stmt = $this->getEntityManager()->getConnection()->prepare($SQL);
    $res  = $stmt->executeQuery($par);
    $data = [];
    while($d = $res->fetchNumeric())
      {
      if(!$total)
        {
        $total  = $d[$columncount];      // total_count column
        }
      if((int)$d[3])
        {
        $cl   = 'text-danger';
        $txt  = 'Inaktiv';
        }
      else
        {
        $cl   = 'text-success';
        $txt  = 'Aktiv';
        }
      $d[3] = "<span class='$cl'>$txt</span>";
      $d[4] = $this->appRuntime->FormatCheckbox(!(int)$d[4]);
      $data[] = $d;
      }
    return [
      'DATA'  => $data,
      'TOTAL' => $total,
      ];
    }
  }
