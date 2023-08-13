<?php declare(strict_types=1);
/**
 * Generic user manager for Symfony 6
 * @author Sascha 'SieGeL' Pfalz <webmaster@in-ovation.de>
 */

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:user')]
class UserManagerCommand extends Command
  {
  /** @var string Name of user class to handle */
  private string $className = User::class;

  /** @var EntityManagerInterface */
  private EntityManagerInterface $em;

  /** @var UserPasswordHasherInterface */
  private UserPasswordHasherInterface $passwordEncoder;

  /** @var array|string[] Roles available in this application */
  private array $roles = ['ROLE_USER','ROLE_ADMIN'];

  /** @var SymfonyStyle $io Helper class for console */
  private SymfonyStyle $io;
  private LoggerInterface $logger;
  
  /**
   * UserManagerCommand constructor.
   * @param EntityManagerInterface $em
   * @param UserPasswordHasherInterface $passwordEncoder
   * @param LoggerInterface $logger
   */
  public function __construct(EntityManagerInterface $em,
                              UserPasswordHasherInterface $passwordEncoder,
                              LoggerInterface $logger)
    {
    $this->em = $em;
    $this->passwordEncoder = $passwordEncoder;
    parent::__construct();
      $this->logger = $logger;
    }

  /**
   * Configure command
   */
  protected function configure():void {
    $this
      // the short description shown while running "php bin/console list"
      ->setDescription('User Manager')

      // the full command description shown when running the command with
      // the "--help" option
      ->setHelp('Simple user manager allows to list/create/update/delete users')
    ;
  }

  /**
   * @param InputInterface $input
   * @param OutputInterface $output
   * @return int
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
    {
    $this->io = new SymfonyStyle($input, $output);
    $this->io->section('User Manager');
    $users = $this->em->getRepository($this->className)->findBy([],['email' => 'asc']);
    $ulist = [];
    foreach($users AS $user)
      {
      $ulist[] = [$user->getId(),$user->getEmail(),join(",",$user->getRoles())];
      }
    $this->io->table(['ID', 'E-MAIL', 'ROLES'],$ulist);
    $output->writeln("");
    $action = $this->io->ask('Enter UserID to view, [c] to create new user, [d] to delete, [q] to quit','q');
    $output->writeln("");
    if(strtolower(sprintf("%s",$action)) === 'c')
      {
      return $this->CreateUser();
      }
    elseif(strtolower(sprintf("%s",$action)) === 'd')
      {
      return $this->deleteUser();
      }
    elseif((int)$action > 0)
      {
      return $this->EditUser((int)$action);
      }
    else
      {
      $this->io->success('Exit');
      }
    return Command::SUCCESS;
    }

  /**
   * Creates new user
   * @return int
   */
  private function CreateUser():int
    {
    $this->io->section("Create new user");
    $this->io->newLine();
    $email = $this->io->ask('Enter e-mail address',null);
    if($email === null)
      {
      return Command::FAILURE;
      }
    $pw = $this->io->askHidden('Enter password');
    if($pw === null)
      {
      return Command::FAILURE;
      }
    $first = $this->io->ask('Enter first name',null);
    if($first === null)
      {
      return Command::FAILURE;
      }
    $last = $this->io->ask('Enter last name',null);
    if($last === null)
      {
      return Command::FAILURE;
      }
    $role = $this->io->choice('Please choose role (ROLE_USER is default)',$this->roles,0);
    /** @var User $user */
    $user = new $this->className;
    $user->setEmail($email)->setFirstName($first)->setLastName($last)->setRoles([$role]);
    $user->setPassword($this->passwordEncoder->hashPassword($user,$pw));
    $this->ShowUser($user);
    $result = $this->io->confirm("Okay to create this user now?", true);
    if($result === false)
      {
      $this->io->success("Aborted!");
      }
    $this->em->persist($user);
    $this->em->flush();
    $this->logger->info("User {$user->getEmail()} created");
    $this->io->success('User successfully created!');
    return Command::SUCCESS;
    }
  
  /**
   * Allows to change the password for a given user
   * @param int $uid
   * @return int
   */
  private function EditUser(int $uid):int
    {
    $user = $this->em->getRepository($this->className)->find($uid);
    if($user === null)
      {
      $this->io->warning("No User found with ID=$uid !");
      return Command::FAILURE;
      }
    $this->ShowUser($user);
    $action = $this->io->confirm("Would you like to set a password for this user?",false);
    if($action === false)
      {
      return Command::SUCCESS;
      }
    $newPW = $this->io->askHidden("Enter password");
    if($newPW === null)
      {
      $this->io->success("Aborted!");
      return Command::SUCCESS;
      }
    $user->setPassword($this->passwordEncoder->hashPassword($user, $newPW));
    $this->em->persist($user);
    $this->em->flush();
    $this->logger->info("Updated password for user #$uid ({$user->getEmail()})");
    return Command::SUCCESS;
    }

  /**
   * Renders informations for given user object.
   * @param User $user
   */
  private function ShowUser(User $user):void
    {
    $uid = (int)$user->getId();
    if(!$uid)
      {
      $uid = "New user!";
      }
      $this->io->horizontalTable(
        ['ID:', 'E-Mail:', 'First name:','Last name:','Roles:','Register date:'],
        [
          [
            $uid,
            $user->getEmail(),
            $user->getFirstName(),
            $user->getLastName(),
            join(",",$user->getRoles()),
            $user->getCreatedOn()->format('d.M.Y H:i:s'),
          ],
        ]
      );
    }
  
  /**
   * Removes user
   * @return int
   */
  private function deleteUser():int
    {
    $action = (int)$this->io->ask('Enter UserID to delete, [0] to quit','0');
    if(!$action)
      {
      $this->io->success("Aborted!");
      return Command::SUCCESS;
      }
    $user = $this->em->getRepository($this->className)->find($action);
    if($user === null)
      {
      $this->io->warning("No User found with ID=$action !");
      return Command::FAILURE;
      }
    $this->ShowUser($user);
    $action = $this->io->confirm("Really delete this user?",false);
    if($action === false)
      {
      $this->io->success("Aborted!");
      return Command::SUCCESS;
      }
    $this->em->remove($user);
    $this->logger->info("User {$user->getEMail()} removed");
    $this->em->flush();
    $this->io->success("User {$user->getEMail()} was successfully removed");
    return Command::SUCCESS;
    }
  
  }
