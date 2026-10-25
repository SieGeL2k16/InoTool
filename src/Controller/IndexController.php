<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\LicenseInfoHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use LogicException;


/**
 * Main index page.
 * @package InoTool
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 * @version 1.0.0 (25-12-2021)
 */
class IndexController extends BaseController
  {
  /**
   * Shows login screen
   * @param AuthenticationUtils $authenticationUtils
   * @return Response
   */
  #[Route('/',name: 'index')]
  public function index(AuthenticationUtils $authenticationUtils):Response
    {
    // get the login error if there is one
    $error = $authenticationUtils->getLastAuthenticationError();
    // last username entered by the user
    $lastUsername = $authenticationUtils->getLastUsername();
    return $this->render('index/index.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

  /**
   * @return Response
   */
  #[Route('/home',name: 'home')]
  public function home():Response
    {
    return $this->render('index/home.html.twig');
    }

  /**
   * Shows about page with version and license informations
   * @param Request $request
   * @param LicenseInfoHelper $licenseInfoHelper
   * @return Response
   */
  #[IsGranted("ROLE_USER")]
  #[Route('/about',name: 'about')]
  public function about(Request $request, LicenseInfoHelper $licenseInfoHelper):Response
    {
    $runtime = $licenseInfoHelper->GetRuntimeInfo();
    $runtime['Webserver'] = (string)$request->server->get('SERVER_SOFTWARE','');
    return $this->render('index/about.html.twig', ['LICENSES' => $licenseInfoHelper->Load(), 'RUNTIME' => $runtime]);
    }

  /**
   * Dummy for logout functionality
   */
  #[Route('/logout',name: 'app_logout')]
  public function logout()
    {
    throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

  /**
   * Refreshes the session every 60 seconds
   */
  #[IsGranted("ROLE_USER")]
  #[Route("/ajax/Ping",name: "ajaxPing",methods: "POST")]
  public function Ping():JsonResponse
    {
    return new JsonResponse(['PONG' => microtime(true)]);
    }

  }
