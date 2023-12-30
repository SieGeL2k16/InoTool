<?php declare(strict_types=1);
/**
 * Helper service for all reporting
 * @todo Refactor all SQL statements - these shouldn't be here at all...
 */
namespace App\Service;

use DateTime;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use IntlDateFormatter;

class ReportingHelper
  {
  private EntityManagerInterface $entity;
  
  /**
   * @param EntityManagerInterface $entity
   */
  public function __construct(EntityManagerInterface $entity)
    {
    $this->entity = $entity;
    }
  
  /**
   * Returns Top-<n> projects grouped by time
   * @param int $uid
   * @param int $limit
   * @return array
   * @throws Exception
   */
  public function getTopWorkingTime(int $uid, int $limit = 10):array
    {
    $SQL = "SELECT  i.TOTSECS,i.PROJECT_NAME,i.CUSTOMER_NAME,calculateProjectEntry(i.TOTSECS,i.WORK_UNIT, i.PAY_PER_WORK_UNIT) AS SALARY
              FROM (
              SELECT 	SUM(pe.WORK_TIME_IN_SECS) AS TOTSECS,
               		    p.PROJECT_NAME,
              		    c.NAME AS CUSTOMER_NAME,
		                  p.work_UNIT,
		                  p.PAY_PER_WORK_UNIT
                FROM	fl_customer c, fl_projects p, fl_project_entries pe
               WHERE	c.ID = p.REF_CUSTOMER_ID
                 AND  p.ID  = pe.REF_PROJECT_ID
                 and  c.REF_USER_ID = :uid
               GROUP BY p.PROJECT_NAME,c.NAME,p.WORK_UNIT,p.PAY_PER_WORK_UNIT
               ORDER BY 1 DESC
                   ) i
             limit :l";
    $stmt = $this->entity->getConnection()->executeQuery($SQL,['uid' => $uid, 'l' => $limit]);
    return $stmt->fetchAllAssociative();
    }
  
  /**
   * Returns Top-<n> projects ordered by money earned
   * @param int $uid
   * @param int $limit
   * @return array
   * @throws Exception
   */
  public function getTopPaidProjects(int $uid, int $limit = 10):array
    {
    $SQL = "SELECT  i.TOTSECS,i.SALARY,i.PROJECT_NAME,i.CUSTOMER_NAME
              FROM (
                SELECT 	SUM(pe.WORK_TIME_IN_SECS) AS TOTSECS,
      		              coalesce(calculateProjectEntry(SUM(pe.WORK_TIME_IN_SECS),p.WORK_UNIT, p.PAY_PER_WORK_UNIT),sum(pe.costs)) AS SALARY,
      		              p.PROJECT_NAME,
      		              c.NAME AS CUSTOMER_NAME
                FROM	fl_customer c , fl_projects p, fl_project_entries pe
                WHERE	c.ID = p.REF_CUSTOMER_ID
                AND  p.ID  = pe.REF_PROJECT_ID
                AND  c.REF_USER_ID = :uid
                GROUP BY p.PROJECT_NAME,c.NAME,p.WORK_UNIT,p.PAY_PER_WORK_UNIT
                ORDER BY 2 DESC NULLS LAST
                ) i
	          limit :l";
    $stmt = $this->entity->getConnection()->executeQuery($SQL,['uid' => $uid, 'l' => $limit]);
    return $stmt->fetchAllAssociative();
    }
  
  /**
   * Returns overview data for given userid
   * @param int $uid
   * @return array
   * @throws Exception
   * @todo Combine min/max queries into one!
   */
  public function getOverview(int $uid):array
    {
    $SQL = "SELECT  i.PROJECTS AS PROJECTS,
                    i.CUSTOMERS AS CUSTOMERS,
                    i.ENTRIES AS ENTRIES,
                    TO_CHAR(i.FIRSTENTRY,'DD.MM.YYYY') AS FIRSTENTRY,
                    TO_CHAR(i.LASTENTRY,'DD.MM.YYYY') AS LASTENTRY,
                    i.LASTENTRY-i.FIRSTENTRY AS MYRANGE
            FROM (
              SELECT  (SELECT COUNT(*) FROM fl_projects p WHERE p.REF_USER_ID=:uid) AS PROJECTS,
                      (SELECT COUNT(*) FROM fl_customer c WHERE c.REF_USER_ID=:uid) AS CUSTOMERS,
                      (SELECT COUNT(*) FROM fl_project_entries pe WHERE pe.REF_USER_ID=:uid) AS ENTRIES,
                      (SELECT MIN(ENTRY_DATE) FROM fl_project_entries WHERE REF_USER_ID=:uid) AS FIRSTENTRY,
                      (SELECT MAX(ENTRY_DATE) FROM fl_project_entries WHERE REF_USER_ID=:uid) AS LASTENTRY
                 ) i";
    $stmt = $this->entity->getConnection()->executeQuery($SQL,['uid' => $uid]);
    return $stmt->fetchAssociative();
    }
  
  /**
   * Returns totals for given userid
   * @param int $uid
   * @return array
   * @throws Exception
   */
  public function getTotals(int $uid):array
    {
    $SQL = "SELECT  SUM(final.TOTAL_SECONDS) AS TOTAL_SECONDS,
                    SUM(final.TOTAL_SALARY) AS TOTAL_SALARY
              FROM  (SELECT SUM(i.TOTSECS) AS TOTAL_SECONDS,SUM(i.SALARY) AS TOTAL_SALARY
                       FROM (SELECT SUM(pe.WORK_TIME_IN_SECS) AS TOTSECS,
          		                      coalesce(calculateProjectEntry(SUM(pe.WORK_TIME_IN_SECS),p.WORK_UNIT, p.PAY_PER_WORK_UNIT),sum(pe.costs)) AS SALARY
                               FROM fl_customer c, fl_projects  p, fl_project_entries pe
                              WHERE c.ID = p.REF_CUSTOMER_ID
                                AND p.ID  = pe.REF_PROJECT_ID
                                AND c.REF_USER_ID = :uid
                              GROUP BY p.WORK_UNIT,p.PAY_PER_WORK_UNIT
                            ) i
                         UNION
                          SELECT 0 AS TOTS,
                                 SUM(case when inv.NO_TAX = false then inv.SUM_BRUTTO else inv.SUM_NETTO end) AS TOTSAL
                            FROM fl_invoices inv
                           WHERE inv.REF_USER_ID  = :uid
                             AND inv.INVOICE_TYPE = 0
                         UNION
                         SELECT 0,
                                SUM(sco.billing_fee) AS SCO_SALARY
                           FROM fl_service_contract_entries sco
                           WHERE sco.REF_USER_ID = :uid
                         ) final";
    $stmt = $this->entity->getConnection()->executeQuery($SQL,['uid' => $uid]);
    return $stmt->fetchAssociative();
    }
  
  /**
   * Returns the month/year as clear-text date string.
   * @input integer $monid The month to convert (format: YYYYMM)
   * @return string The formatted string localized in format <month> <year>.
   */
  public function getMonthText(string $locale, int|string $monid):string
    {
    $df =  new IntlDateFormatter($locale,IntlDateFormatter::FULL,IntlDateFormatter::NONE,null,IntlDateFormatter::GREGORIAN, 'MMMM yyyy');
    return $df->format(DateTime::createFromFormat('Ym',(string)$monid));
    }
  
  /**
   * Returns a text describing the given date range between sdate and edate.
   * @param string $sdate Startdate, can be empty
   * @param string $edate Enddate, can be empty
   * @return string Text with specified date range
   */
  public function getDateRangeText(string $sdate,string $edate):string
    {
    $trange = '';
    if($sdate == '' && $edate == '')
      {
      $trange = 'Alle erfassten Einträge';
      }
    else if($sdate != '' && $edate == '')
      {
      $trange = sprintf('Seit dem %s',$sdate);
      }
    else if($sdate == '' && $edate != '')
      {
      $trange = sprintf('Bis einschliesslich %s',$edate);
      }
    else
      {
      $trange = sprintf("Vom %s bis einschliesslich %s",$sdate,$edate);
      }
    return $trange;
    }
  
  }
