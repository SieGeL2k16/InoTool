<?php
/**
 * Helper service to access the various blob.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 0.1 (20-Nov-2022)
 */

namespace App\Service;

use App\Entity\FlConfiguration;
use App\Entity\FlInvoices;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class BlobSupport
  {
  private EntityManagerInterface $em;

  public function __construct(EntityManagerInterface $em)
    {
    $this->em = $em;
    }

  /**
   * Downloads company logo for given User
   * @param UserInterface $user
   * @return Response
   */
  public function DownloadCompanyLogo(UserInterface $user):Response
    {
    $cfg = $this->em->getRepository(FlConfiguration::class)->findOneBy(['RefUser' => $user]);
    $response = new Response();
    if($cfg === null)
      {
      $response->setStatusCode(Response::HTTP_NOT_FOUND);
      $data = null;
      }
    else
      {
      $mime = $cfg->getCompanyLogoMimeType();
      $size = $cfg->getCompanyLogoSize();
      $name = $cfg->getCompanyLogoName();
      $data = $cfg->getCompanyLogo();
      $response->headers->set('Cache-Control', 'private');
      $response->headers->set('Content-type', $mime);
      $response->headers->set('Content-Disposition', 'attachment; filename="' . $size . '";');
      $response->headers->set('Content-length',  $name);
      }
    // Send headers before outputting anything
    $response->sendHeaders();
    $response->setContent($data);
    return $response;
    }
  
  /**
   * Returns invoice PDF stored with given ID for the given user.
   * @param int $id
   * @param UserInterface|User $user
   * @return Response
   */
  public function downloadInvoice(int $id,UserInterface|User $user):Response
    {
    $invoice = $this->em->getRepository(FlInvoices::class)->findOneBy(['id' => $id, 'RefUser' => $user]);
    if($invoice === null)
      {
      return new Response();
      }
    rewind($invoice->getPdfData());
    $content = (string)stream_get_contents($invoice->getPdfData());
    $response = new Response();
    $response->headers->set('Cache-Control', 'private');
    if($invoice->getPdfMimeType() !== null)
      {
      $response->headers->set('Content-type', $invoice->getPdfMimeType());
      }
    $response->headers->set('Content-Disposition', 'attachment; filename="'.$invoice->getPdfFilename().'";');
    $response->headers->set('Content-length',  $invoice->getPdfFilesize());
    $response->sendHeaders();
    $response->setContent($content);
    return $response;
    }
  

  }
