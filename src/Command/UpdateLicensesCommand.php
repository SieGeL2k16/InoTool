<?php declare(strict_types=1);
/**
 * Updates version and license informations shown on the about page.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (25-10-2026)
 */

namespace App\Command;

use App\Service\LicenseInfoHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(name: 'app:update-licenses')]
class UpdateLicensesCommand extends Command
  {
  private LicenseInfoHelper $licenseInfoHelper;

  /**
   * @param LicenseInfoHelper $licenseInfoHelper
   */
  public function __construct(LicenseInfoHelper $licenseInfoHelper)
    {
    $this->licenseInfoHelper = $licenseInfoHelper;
    parent::__construct();
    }

  /**
   * Configure command
   */
  protected function configure():void
    {
    $this
      ->setDescription('Updates version and license informations of all used components')
      ->setHelp('Collects composer packages and frontend libraries and writes them to '.LicenseInfoHelper::DATA_FILE)
    ;
    }

  /**
   * @param InputInterface $input
   * @param OutputInterface $output
   * @return int
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
    {
    $io = new SymfonyStyle($input, $output);
    $io->section('Update license informations');
    try
      {
      $data = $this->licenseInfoHelper->Update();
      }
    catch(Throwable $e)
      {
      $io->error($e->getMessage());
      return Command::FAILURE;
      }
    $io->table(['Type', 'Components'], [
      ['Composer', count($data['composer'])],
      ['Frontend', count($data['frontend'])],
    ]);
    // Report components with incomplete informations
    $incomplete = [];
    foreach(array_merge($data['composer'], $data['frontend']) AS $c)
      {
      if($c['version'] === '' || $c['license'] === '')
        {
        $incomplete[] = $c['name'];
        }
      }
    if(count($incomplete) > 0)
      {
      $io->warning('Missing version or license for: '.implode(', ', $incomplete));
      }
    if(count($data['unknown']) > 0)
      {
      $io->warning('Unknown frontend files (add them to LicenseInfoHelper::FRONTEND_LIBS): '.implode(', ', $data['unknown']));
      }
    $io->success('Saved to '.LicenseInfoHelper::DATA_FILE);
    return Command::SUCCESS;
    }

  }
