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
  const PAGE_MARGIN_X = 22;

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
   * @param bool|int $pdfa
   * @return TCPDF
   */
  public function Init(string $orientation = 'P', string $unit = 'mm', mixed $format = 'A4', bool|int $pdfa = false): TCPDF
    {
    $this->tcpdf = new TCPDF($orientation,$unit,$format,true,'UTF-8',false,$pdfa);
    return $this->tcpdf;
    }

  public function SetMetaData(User $user):TCPDF
    {
    $this->tcpdf->setAuthor($user->getFirstName().' '.$user->getLastName());
    $this->tcpdf->setCreator("InoTool ".$this->bag->get('APP_VERSION'));
    $this->tcpdf->setPrintHeader(false);
    $this->tcpdf->setPrintFooter(false);
    return $this->tcpdf;
    }

  /**
   * Downloads PDF stored in $buffer
   * @param string $filename
   * @param string $buffer
   * @return Response
   */
  public function Download(string $filename, string $buffer):Response
    {
    $response = new Response();
    $response->headers->set('Cache-Control', 'must-revalidate');
    $response->headers->set('Content-type', 'application/octet-stream' );
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '";');
    $response->headers->set('Content-length',  sprintf("%s",strlen($buffer)));
    $response->headers->set('Content-Transfer-Encoding', 'binary');
    // Send headers before outputting anything
    $response->sendHeaders();
    $response->setContent($buffer);
    return $response;
    }

  /**
   * Adds folding lines to the current page.
   */
  public function addFoldLines():void
    {
    $this->tcpdf->SetDrawColor(200,200,200);
    $this->tcpdf->Line(3,100.5,7,100.5);
    $this->tcpdf->Line(3,140.85,7,140.85);
    $this->tcpdf->Line(3,210,7,210);
    }

  }
