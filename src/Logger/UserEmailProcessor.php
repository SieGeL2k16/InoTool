<?php declare(strict_types=1);
namespace App\Logger;

use Monolog\LogRecord;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\SecurityBundle\Security;
/**
 * Writes E-Mail and Ip Adress of user to logfile
 */
class UserEmailProcessor
  {
  private Security $security;
  private RequestStack $requestStack;

  /**
   * @param Security $security
   * @param RequestStack $requestStack
   */
  public function __construct(Security $security, RequestStack $requestStack)
    {
    $this->security = $security;
    $this->requestStack = $requestStack;
    }

  /**
   * this method is called for each log record; optimize it to not hurt performance
   */
  public function __invoke(array|LogRecord $record): array|LogRecord
    {
    try
      {
      $user = $this->security->getUser();
      if($user === null)
        {
        throw new SessionNotFoundException("No user found!");
        }
      }
    catch (SessionNotFoundException $e)
      {
      return $record;
      }
    if($record instanceof LogRecord)
      {
      $record->extra['email'] = $user->getUserIdentifier();
      $record->extra['ipaddr'] = $this->requestStack->getMainRequest()->getClientIp();
      }
    else
      {
      $record['extra']['email'] = $user->getUserIdentifier();
      $record['extra']['ipaddr'] = $this->requestStack->getMainRequest()->getClientIp();
      }
    return $record;
    }
  }
