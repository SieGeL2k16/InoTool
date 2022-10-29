<?php declare(strict_types=1);
/**
 * Helper methods to implement TCPDF functionality.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (29-Oct-2022)
 */

namespace App\Service;

use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use TCPDF;


class tcpdf_helper
  {
  /** Page margin for left & right */
  const PAGE_MARGIN_X = 24;

  /** @var TCPDF $tcpdf */
  private TCPDF $tcpdf;

  /** @var ContainerBagInterface $bag */
  private ContainerBagInterface $bag;
  
  public function __construct(ContainerBagInterface $bag)
    {
    $this->bag = $bag;
    }
  
  /**
   * Use this to initialize the TCPDF class.
   * @param string $orientation
   * @param string $unit
   * @param mixed $format
   * @param bool $pdfa
   * @return TCPDF
   */
  public function Init(string $orientation = 'P', string $unit = 'mm', mixed $format = 'A4', bool $pdfa = false): TCPDF
    {
    $this->tcpdf = new TCPDF($orientation,$unit,$format,true,'UTF-8',false,$pdfa);
    return $this->tcpdf;
    }

  public function SetMetaData(User|UserInterface $user):TCPDF
    {
    $this->tcpdf->setAuthor($user->getFirstName().' '.$user->getLastName());
    $this->tcpdf->setCreator("InoTool ".$this->bag->get('APP_VERSION'));
    $this->tcpdf->setPrintHeader(false);
    $this->tcpdf->setPrintFooter(false);
    return $this->tcpdf;
    }

  public function Download(string $filename, string $buffer):Response
    {
    $response = new Response();
    $response->headers->set('Cache-Control', 'private');
    $response->headers->set('Content-type', 'application/pdf' );
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '";');
    $response->headers->set('Content-length',  sprintf("%s",strlen($buffer)));
    // Send headers before outputting anything
    $response->sendHeaders();
    $response->setContent($buffer);
    return $response;
    }
  
  }
