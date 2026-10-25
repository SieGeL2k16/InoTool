<?php declare(strict_types=1);
/**
 * Collects version and license informations of all components used in this app.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (25-10-2026)
 */

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Kernel;
use Throwable;

class LicenseInfoHelper
  {
  /** @var string Data file, relative to project dir */
  public const string DATA_FILE = 'config/licenses.json';

  /** @var string[] Directories below public/ scanned for frontend components */
  private const array FRONTEND_DIRS = ['js/vendor', 'css/vendor', 'css/webfonts'];

  /**
   * Known frontend libraries.
   * files: regex for paths relative to public/, version/info: regex applied to file content (null = not detectable)
   * @var array<string,array{name:string,files:string,version:string|null,info?:string,license:string,authors:string,url:string}>
   */
  private const array FRONTEND_LIBS = [
    'bootstrap' => [
      'name'    => 'Bootstrap',
      'files'   => '#^(js|css)/vendor/bootstrap[.\w]*\.(js|css|map)$#',
      'version' => '#Bootstrap\s+v(\d[\w.\-]*)#',
      'license' => 'MIT',
      'authors' => 'The Bootstrap Authors',
      'url'     => 'https://getbootstrap.com/',
    ],
    'jquery' => [
      'name'    => 'jQuery',
      'files'   => '#^js/vendor/jquery-[\d.]+(\.min)?\.(js|map)$#',
      'version' => '#jQuery (?:JavaScript Library )?v(\d[\w.\-]*)#',
      'license' => 'MIT',
      'authors' => 'OpenJS Foundation and other contributors',
      'url'     => 'https://jquery.com/',
    ],
    'jquery-validation' => [
      'name'    => 'jQuery Validation Plugin',
      'files'   => '#^js/vendor/(jquery\.validate|additional-methods)(\.min)?\.js$#',
      'version' => '#jQuery Validation Plugin - v(\d[\w.\-]*)#',
      'license' => 'MIT',
      'authors' => 'Jörn Zaefferer',
      'url'     => 'https://jqueryvalidation.org/',
    ],
    'jquery-toast' => [
      'name'    => 'jQuery Toast Plugin',
      'files'   => '#^(js|css)/vendor/jquery\.toast[.\w]*\.(js|css)$#',
      'version' => null,
      'license' => 'MIT',
      'authors' => 'Kamran Ahmed',
      'url'     => 'https://github.com/kamranahmedse/jquery-toast-plugin',
    ],
    'datatables' => [
      'name'    => 'DataTables',
      'files'   => '#^(js|css)/vendor/datatables[.\w]*\.(js|css|json)$#',
      'version' => '#DataTables (\d[\w.\-]*)#',
      'info'    => '#Included libraries:\s*\*\s*([^\r\n]+)#',
      'license' => 'MIT',
      'authors' => 'SpryMedia Ltd',
      'url'     => 'https://datatables.net/',
    ],
    'select2' => [
      'name'    => 'Select2',
      'files'   => '#^(js/vendor/(select2[.\w]*\.js|i18n/[\w\-]+\.js)|css/vendor/select2(\.min)?\.css)$#',
      'version' => '#Select2 (\d[\w.\-]*)#',
      'license' => 'MIT',
      'authors' => 'Kevin Brown, Igor Vaynberg and contributors',
      'url'     => 'https://select2.org/',
    ],
    'select2-bootstrap-5-theme' => [
      'name'    => 'Select2 Bootstrap 5 Theme',
      'files'   => '#^css/vendor/select2-bootstrap-5-theme(\.min)?\.css$#',
      'version' => '#Bootstrap 5 theme v(\d[\w.\-]*)#',
      'license' => 'MIT',
      'authors' => 'apalfrey',
      'url'     => 'https://github.com/apalfrey/select2-bootstrap-5-theme',
    ],
    'sweetalert2' => [
      'name'    => 'SweetAlert2',
      'files'   => '#^js/vendor/sweetalert2[.\w]*\.js$#',
      'version' => '#sweetalert2 v(\d[\w.\-]*)#',
      'license' => 'MIT',
      'authors' => 'Limon Monte',
      'url'     => 'https://sweetalert2.github.io/',
    ],
    'chartjs' => [
      'name'    => 'Chart.js',
      'files'   => '#^js/vendor/chart[.\w]*\.js$#',
      'version' => '#Chart\.js v(\d[\w.\-]*)#',
      'license' => 'MIT',
      'authors' => 'Chart.js Contributors',
      'url'     => 'https://www.chartjs.org/',
    ],
    'luxon' => [
      'name'    => 'Luxon',
      'files'   => '#^js/vendor/luxon[.\w]*\.js$#',
      'version' => '#VERSION="(\d[\w.\-]*)"#',
      'license' => 'MIT',
      'authors' => 'Isaac Cambron and contributors',
      'url'     => 'https://moment.github.io/luxon/',
    ],
    'fontawesome' => [
      'name'    => 'Font Awesome Free',
      'files'   => '#^css/(vendor/all(\.min)?\.css|webfonts/fa-[\w\-]+\.(ttf|woff2?))$#',
      'version' => '#Font Awesome Free (\d[\w.\-]*)#',
      'license' => 'Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT',
      'authors' => 'Fonticons, Inc.',
      'url'     => 'https://fontawesome.com/',
    ],
  ];

  private string $projectDir;
  private EntityManagerInterface $entity;

  /**
   * @param string $projectDir
   * @param EntityManagerInterface $entity
   */
  public function __construct(#[Autowire('%kernel.project_dir%')] string $projectDir, EntityManagerInterface $entity)
    {
    $this->projectDir = $projectDir;
    $this->entity = $entity;
    }

  /**
   * Collects all component informations and writes them to the data file.
   * @return array{generated:string,composer:array<int,array<string,mixed>>,frontend:array<int,array<string,mixed>>,unknown:string[]}
   * @throws JsonException
   */
  public function Update():array
    {
    $frontend = $this->CollectFrontend();
    $data = [
      'generated' => date(DATE_ATOM),
      'composer'  => $this->CollectComposer(),
      'frontend'  => $frontend['libs'],
      'unknown'   => $frontend['unknown'],
      ];
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    if(file_put_contents($this->GetDataFile(), $json."\n") === false)
      {
      throw new RuntimeException('Unable to write '.$this->GetDataFile());
      }
    return $data;
    }

  /**
   * Returns stored component informations, null if data file is missing or invalid.
   * @return array<string,mixed>|null
   */
  public function Load():?array
    {
    try
      {
      return $this->ReadJson($this->GetDataFile());
      }
    catch(Throwable)
      {
      return null;
      }
    }

  /**
   * Returns versions of the runtime environment.
   * @return array<string,string>
   */
  public function GetRuntimeInfo():array
    {
    try
      {
      $dbVersion = 'PostgreSQL '.$this->entity->getConnection()->fetchOne('SHOW server_version');
      }
    catch(Throwable)
      {
      $dbVersion = '';
      }
    return [
      'PHP'       => PHP_VERSION,
      'Symfony'   => Kernel::VERSION,
      'Datenbank' => $dbVersion,
      ];
    }

  /**
   * Returns absolute path of data file.
   * @return string
   */
  public function GetDataFile():string
    {
    return $this->projectDir.'/'.self::DATA_FILE;
    }

  /**
   * Reads all installed composer packages.
   * @return array<int,array<string,mixed>>
   * @throws JsonException
   */
  private function CollectComposer():array
    {
    $installed = $this->ReadJson($this->projectDir.'/vendor/composer/installed.json');
    // Composer 1 stores a plain list, composer 2 uses "packages"
    $packages  = $installed['packages'] ?? $installed;
    $devNames  = $installed['dev-package-names'] ?? [];
    $root      = $this->ReadJson($this->projectDir.'/composer.json');
    $direct    = array_keys(array_merge($root['require'] ?? [], $root['require-dev'] ?? []));
    $result    = [];
    foreach($packages AS $pkg)
      {
      $name = (string)$pkg['name'];
      $result[] = [
        'name'        => $name,
        'version'     => ltrim((string)($pkg['version'] ?? ''), 'v'),
        'license'     => implode(', ', (array)($pkg['license'] ?? [])),
        'authors'     => $this->FormatAuthors($pkg['authors'] ?? []),
        'url'         => $this->GetPackageUrl($pkg),
        'description' => (string)($pkg['description'] ?? ''),
        'direct'      => in_array($name, $direct, true),
        'dev'         => in_array($name, $devNames, true),
        ];
      }
    usort($result, fn(array $a, array $b):int => strcasecmp($a['name'], $b['name']));
    return $result;
    }

  /**
   * Scans frontend vendor directories and maps files to known libraries.
   * @return array{libs:array<int,array<string,mixed>>,unknown:string[]}
   */
  private function CollectFrontend():array
    {
    $libs    = [];
    $unknown = [];
    foreach($this->GetFrontendFiles() AS $file)
      {
      $found = false;
      foreach(self::FRONTEND_LIBS AS $key => $lib)
        {
        if(preg_match($lib['files'], $file) === 1)
          {
          $libs[$key][] = $file;
          $found = true;
          break;
          }
        }
      if($found === false)
        {
        $unknown[] = $file;
        }
      }
    $result = [];
    foreach($libs AS $key => $files)
      {
      $lib = self::FRONTEND_LIBS[$key];
      $result[] = [
        'name'        => $lib['name'],
        'version'     => $lib['version'] !== null ? $this->DetectFromFiles($files, $lib['version']) : '',
        'license'     => $lib['license'],
        'authors'     => $lib['authors'],
        'url'         => $lib['url'],
        'description' => isset($lib['info']) ? $this->DetectFromFiles($files, $lib['info']) : '',
        'files'       => $files,
        ];
      }
    usort($result, fn(array $a, array $b):int => strcasecmp($a['name'], $b['name']));
    return ['libs' => $result, 'unknown' => $unknown];
    }

  /**
   * Returns all files of the frontend vendor directories, relative to public/.
   * @return string[]
   */
  private function GetFrontendFiles():array
    {
    $public = $this->projectDir.'/public/';
    $files  = [];
    foreach(self::FRONTEND_DIRS AS $dir)
      {
      if(!is_dir($public.$dir))
        {
        continue;
        }
      $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($public.$dir, \FilesystemIterator::SKIP_DOTS));
      foreach($it AS $info)
        {
        /** @var \SplFileInfo $info */
        if($info->isFile())
          {
          $files[] = str_replace('\\', '/', substr($info->getPathname(), strlen($public)));
          }
        }
      }
    sort($files);
    return $files;
    }

  /**
   * Returns first match of $regex in the given js/css files, empty string if nothing found.
   * @param string[] $files
   * @param string $regex
   * @return string
   */
  private function DetectFromFiles(array $files, string $regex):string
    {
    foreach($files AS $file)
      {
      if(!preg_match('#\.(js|css)$#', $file))
        {
        continue;
        }
      $content = (string)file_get_contents($this->projectDir.'/public/'.$file);
      if(preg_match($regex, $content, $m) === 1)
        {
        return trim($m[1]);
        }
      }
    return '';
    }

  /**
   * Returns author names of a composer package as comma separated string.
   * @param array<int,array<string,string>> $authors
   * @return string
   */
  private function FormatAuthors(array $authors):string
    {
    return implode(', ', array_filter(array_map(fn(array $a):string => $a['name'] ?? '', $authors)));
    }

  /**
   * Returns best available weblink for a composer package.
   * @param array<string,mixed> $pkg
   * @return string
   */
  private function GetPackageUrl(array $pkg):string
    {
    $url = $pkg['homepage'] ?? $pkg['support']['source'] ?? $pkg['source']['url'] ?? '';
    if($url === '')
      {
      return 'https://packagist.org/packages/'.$pkg['name'];
      }
    return (string)preg_replace('#\.git$#', '', (string)$url);
    }

  /**
   * Reads and decodes a JSON file.
   * @param string $file
   * @return array<mixed>
   * @throws JsonException
   */
  private function ReadJson(string $file):array
    {
    if(!is_readable($file))
      {
      throw new RuntimeException("File $file not found");
      }
    return (array)json_decode((string)file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
    }

  }
