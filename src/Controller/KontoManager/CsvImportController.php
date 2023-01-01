<?php
/**
 * CSV Importer methods.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (07-Jan-2022)
 */

namespace App\Controller\KontoManager;

use App\Entity\AccountBankAccounts;
use App\Entity\AccountData;
use App\Service\kmImportFilter;
use DateTime;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_USER")]
class CsvImportController extends AbstractController
  {
  const ACT_NAV = 'import';
  
  /** @var int Variables are set during format detection */
  private int $DB_ACC_TYPE;
  private int $DB_ACC_PERSON;
  private int $DB_ACC_TEXT;
  private int $DB_ACC_SOLL;
  private int $DB_ACC_HABEN;
  private int $DB_ACC_CURRENCY;
  private int $DB_OLD_FORMAT;
  private int $DB_ACC_RECIPIENT_ACCOUNT;

  
  /**
   * Shows import form.
   * @return Response
   */
  #[Route("/kontomanager/csv-import",name: 'km_csvImport')]
  public function importForm():Response
    {
    return $this->render('kontomanager/csv_importer_form.html.twig',[
      'ACTNAV' => self::ACT_NAV,
    ]);
    }
  
  /**
   * Handles import of csv file for selected bank
   * @param Request $request
   * @param LoggerInterface $logger
   * @param ManagerRegistry $registry
   * @param kmImportFilter $importFilter
   * @return Response
   * @throws Exception
   */
  #[Route("/kontomanager/import-csv",name: 'km_importCsv',methods: ["POST"])]
  public function doImport(Request $request, LoggerInterface $logger,
                           ManagerRegistry $registry,kmImportFilter $importFilter):Response
    {
    $st = microtime(true);
    $importFilter->Init($this->getUser());
    $banktype = $request->get('CSVFORMAT');
    $csvfile  = $request->files->get('CSVDATA');
    if($csvfile === null)
      {
      $logger->error("No file found - Import not possible!");
      $this->addFlash('error','Keine CSV Datei gefunden?! ');
      return $this->redirectToRoute('km_csvImport');
      }
    $bank = $registry->getRepository(AccountBankAccounts::class)->findOneBy(['RefUser' => $this->getUser(),'BankShortcut' => $banktype]);
    if($bank === null)
      {
      $logger->error("Unknown Bank Shortcut \"$banktype\" !");
      $this->addFlash('error',"Keine Bank mit dem Kürzel \"banktype\" gefunden!");
      return $this->redirectToRoute('km_csvImport');
      }
    switch($banktype)
      {
      case  'DB':
        try
          {
          $result = $this->doImportDb($csvfile,$bank,$importFilter,$registry);
          }
        catch(Exception $exception)
          {
          $logger->error("DB Import error: ".$exception->getMessage());
          $this->addFlash('error',"Import Fehler aufgetreten: ".$exception->getMessage());
          return $this->redirectToRoute('km_csvImport');
          }
        break;
        
      case  'TargoBank':
        $result = $this->doImportTargo($csvfile,$bank,$importFilter,$registry);
        break;

      default:
        $logger->error("Unsupported banktype \"$banktype\" - import not possible!");
        $this->addFlash('error',"Nicht unterstützte Bank \"$banktype\" - Import nicht möglich!");
        return $this->redirectToRoute('km_csvImport');
      }
    return $this->render('kontomanager/csv_importer_done.html.twig',[
      'ACTNAV'  => self::ACT_NAV,
      'RESULT'  => $result,
      'IMPTIME' => sprintf("%5.3fs",microtime(true) - $st),
      ]);
    }
  
  /**
   * Handles import of csv file from "Deutsche Bank"
   * NOTE: DB has released various changes during the years, this code tries to
   *       compensate and normalize
   * @todo Not all fields are parsed, maybe all other fields should be included, too
   * @param UploadedFile $csvfile
   * @param AccountBankAccounts $bank
   * @param kmImportFilter $importFilter
   * @param ManagerRegistry $registry
   * @return array
   * @throws Exception
   */
  private function doImportDb(UploadedFile $csvfile,AccountBankAccounts &$bank,
                              kmImportFilter $importFilter,ManagerRegistry $registry):array
    {
    $result       = [];
    $csvdata      = file($csvfile->getRealPath());
    $csvheadFound = FALSE;
    foreach($csvdata as $item)
      {
      $csv = str_getcsv($item,";");
      if(!preg_match("/^\d{2}\.\d{2}\.\d{4}$/",$csv[0]))
        {
        if($csvheadFound == FALSE && preg_match("/^Buchungstag.*/",$csv[0]))
          {
          $csvheadFound = TRUE;
          if($this->dbFormatSelect($csv) === false)
            {
            throw new Exception("CSV Dateiformat der Deutschen Bank nicht erkannt!?");
            }
          }
        if(trim($csv[0]) === "Kontostand")
          {
          $bank->setBalance(str_replace(',','.',str_replace('.','',trim($csv[4]))));
          $bank->setBalanceDate(new DateTime($csv[1]));
          $registry->getManager()->persist($bank);
          $registry->getManager()->flush();
          }
        continue;
        }
      $acctext = '';
      // Handle differencies in the CSV file...
      switch($this->DB_OLD_FORMAT)
        {
        case 0:
        case 2:
          if(trim($csv[$this->DB_ACC_TYPE]) != '')
            {
            if($csv[$this->DB_ACC_TYPE] == 'SEPA-Lastschrift')
              {
              // Work-around for Deutsche Bank CSV export, they are constantly changing the format...:(
              $csv[$this->DB_ACC_TYPE] = 'SEPA-Lastschrift von';
              }
            $acctext = sprintf("%s ", $csv[$this->DB_ACC_TYPE]);
            }
          if(trim($csv[$this->DB_ACC_PERSON]) != '')
            {
            if(substr($csv[$this->DB_ACC_TEXT],0,strlen($csv[$this->DB_ACC_PERSON])) != $csv[$this->DB_ACC_PERSON])
              {
              $acctext.= $csv[$this->DB_ACC_PERSON].' ';
              }
            }
          $acctext.= trim($csv[$this->DB_ACC_TEXT]);
          $acctext = trim($acctext);
          break;
    
        case  1:
          $acctext = trim($csv[$this->DB_ACC_TEXT]);
          break;
        }
      if(trim($csv[$this->DB_ACC_SOLL]) == "")
        {
        $field = $this->DB_ACC_HABEN;
        $isIncome = 'true';
        }
      else
        {
        $field = $this->DB_ACC_SOLL;
        $isIncome = 'false';
        }
      $accsum = str_replace(',','.',str_replace('.','',trim($csv[$field])));
      $bdate  = new DateTimeImmutable($csv[0]);
      $data = [
        'bdate'     => $bdate->format('Y-m-d').' 00:00:00',
        'isin'      => $isIncome,
        'amount'    => $accsum,
        'currency'  => trim($csv[$this->DB_ACC_CURRENCY]),
        'descr'     => iconv('iso-8859-1','utf-8',$acctext),
        'accnum'    => $bank->getIban(),
        'bankid'    => $bank->getId(),
        'uid'       => $this->getUser()->getId(),
        'catid'     => $importFilter->CheckFilter($acctext),
        'recpacc'   => trim($csv[$this->DB_ACC_RECIPIENT_ACCOUNT]),
        ];

      $rc = $registry->getRepository(AccountData::class)->Insert($data);
      if(!$rc)  // Already in database
        {
        $status = 1;
        $stext  = 'Bereits vorhanden';
        }
      else
        {
        $status = 0;
        $stext  = 'Okay';
        if((int)$data['catid'])
          {
          $stext.=" (Kategorie: ".$importFilter->GetCatnameById((int)$data['catid']).")";
          }
        }
      $result[] = [
        'BDATE'   => $bdate->format('d.m.Y'),
        'TXT'     => $data['descr'],
        'SUM'     => $accsum,
        'IBAN'    => $data['accnum'],
        'RESULT'  => $stext,
        'STATUS'  => $status,
        ];
      }
    return $result;
    }
  
  /**
   * Check for given format, before SEPA Deutsche Bank only provided 6 fields,
   * after SEPA switch this increased to 16
   * @param array $csvheader
   * @return bool FALSE if unknown format is found, else true
   */
  private function dbFormatSelect(array $csvheader): bool
    {
    if(count($csvheader) == 6)
      {
      $this->DB_ACC_TEXT      = 2;
      $this->DB_ACC_SOLL      = 3;
      $this->DB_ACC_HABEN     = 4;
      $this->DB_ACC_CURRENCY  = 5;
      $this->DB_OLD_FORMAT    = 1;
      }
    // New format with SEPA data
    elseif(count($csvheader) == 16)
      {
      $this->DB_ACC_TYPE      = 2;
      $this->DB_ACC_PERSON    = 3;
      $this->DB_ACC_TEXT      = 4;
      $this->DB_ACC_SOLL      = 13;
      $this->DB_ACC_HABEN     = 14;
      $this->DB_ACC_CURRENCY  = 15;
      $this->DB_OLD_FORMAT    = 0;
      }
    elseif(count($csvheader) == 17)
      {
      $this->DB_ACC_TYPE      = 2;
      $this->DB_ACC_PERSON    = 3;
      $this->DB_ACC_TEXT      = 4;
      $this->DB_ACC_SOLL      = 14;
      $this->DB_ACC_HABEN     = 15;
      $this->DB_ACC_CURRENCY  = 16;
      $this->DB_OLD_FORMAT    = 2;
      }
    elseif(count($csvheader) == 18)
      {
      $this->DB_ACC_TYPE      = 2;
      $this->DB_ACC_PERSON    = 3;
      $this->DB_ACC_TEXT      = 4;
      $this->DB_ACC_SOLL      = 15;
      $this->DB_ACC_HABEN     = 16;
      $this->DB_ACC_CURRENCY  = 17;
      $this->DB_OLD_FORMAT    = 2;
      $this->DB_ACC_RECIPIENT_ACCOUNT = 5;
      }
    else
      {
      return false;
      }
    return true;
    }
  
  /**
   * Imports TargoBank CSV Files.
   * NOTE: This code was last tested in 2011, so it is more than likely that actual csv data from Targo Bank won't work anymore!
   * @param UploadedFile $csvfile
   * @param AccountBankAccounts $bank
   * @param kmImportFilter $importFilter
   * @param ManagerRegistry $registry
   * @return array
   * @throws Exception
   */
  private function doImportTargo(UploadedFile $csvfile,AccountBankAccounts $bank,
                                 kmImportFilter $importFilter,ManagerRegistry $registry):array
    {
    $csvdata  = file($csvfile->getRealPath());
    $result   = [];
    foreach($csvdata as $item)
      {
      // Check if we have a file with the "new" CSV export format, which is rather strange to me, a line looks like this:
      // 01234567890
      // 31.08.2007,"VERSICHERUNGSBEITRAG","-13,25","","",'2807722127'
      if(substr($item, 0, 1) != '"')
        {
        // Split first the string so that we have the date string separate from the rest:
        $mysqldatum   = substr($item, 6, 4) . '-' . substr($item, 3, 2) . '-' . substr($item, 0, 2) . ' 00:00:00';
        $dataline     = substr($item, 11);
        $data         = explode("\",\"", $dataline);      // Split the string on '","' boundary
        $buchungstext = preg_replace("/\"/", "", $data[0]);
        $summe        = str_replace(",", ".", str_replace(".", "", preg_replace("/\"/", "", $data[1])));
        }
      else
        {
        $data = explode("\",\"", $item);      // Split the string on '","' boundary
        // Citibank delivers dates in german notation DD.MM.YYYY, we rebuild this to have a mysql valid date string:
        $datum        = explode(".", preg_replace("/\"/", "", $data[0]));
        $mysqldatum   = $datum[2] . "-" . $datum[1] . "-" . $datum[0] . " 00:00:00";
        $buchungstext = preg_replace("/\"/", "", $data[1]);
        $summe        = str_replace(",", ".", str_replace(".", "", preg_replace("/\"/", "", $data[2])));
        }
      $data = [
        'bdate'     => $mysqldatum,
        'isin'      => str_starts_with($summe, '-'),
        'amount'    => $summe,
        'currency'  => 'EUR',   // Old format does not have any currency included
        'descr'     => iconv('iso-8859-1','utf-8',$buchungstext),
        'accnum'    => $bank->getIban(),
        'bankid'    => $bank->getId(),
        'uid'       => $this->getUser()->getId(),
        'catid'     => $importFilter->CheckFilter($buchungstext),
        ];
      $rc = $registry->getRepository(AccountData::class)->Insert($data);
      if(!$rc)  // Already in database
        {
        $status = 1;
        $stext  = 'Bereits vorhanden';
        }
      else
        {
        $status = 0;
        $stext  = 'Okay';
        if((int)$data['catid'])
          {
          $stext.=" (Kategorie: ".$importFilter->GetCatnameById((int)$data['catid']).")";
          }
        }
      $result[] = [
        'BDATE'   => (new DateTime($data['bdate']))->format('d.m.Y'),
        'TXT'     => $data['descr'],
        'SUM'     => $summe,
        'IBAN'    => $data['accnum'],
        'RESULT'  => $stext,
        'STATUS'  => $status,
        ];
      }
    return $result;
    }
  }

/*
 DB-Format=2:
 array:18 [▼
  0 => "Buchungstag"
  1 => "Wert"
  2 => "Umsatzart"
  3 => b"Begünstigter / Auftraggeber"
  4 => "Verwendungszweck"
  5 => "IBAN"
  6 => "BIC"
  7 => "Kundenreferenz"
  8 => "Mandatsreferenz "
  9 => b"Gläubiger ID"
  10 => b"Fremde Gebühren"
  11 => "Betrag"
  12 => b"Abweichender Empfänger"
  13 => b"Anzahl der Aufträge"
  14 => "Anzahl der Schecks"
  15 => "Soll"
  16 => "Haben"
  17 => b"Währung"
]
 */
