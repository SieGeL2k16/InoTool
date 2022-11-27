<?php declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
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
  
  /**
   * UserManagerCommand constructor.
   * @param EntityManagerInterface $em
   * @param UserPasswordHasherInterface $passwordEncoder
   */
  public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordEncoder)
    {
    $this->em = $em;
    $this->passwordEncoder = $passwordEncoder;
    parent::__construct();
    }

  /**
   * Configure command
   */
  protected function configure() {
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
    $this->io->section('User Manager - Show existing users');
    $users = $this->em->getRepository($this->className)->findBy([],['email' => 'desc']);
    $ulist = [];
    foreach($users AS $user)
      {
      $ulist[] = [$user->getId(),$user->getEmail(),join(",",$user->getRoles())];
      }
    $this->io->table(['ID', 'E-MAIL', 'ROLES'],$ulist);
    $output->writeln("");
    $action = $this->io->ask('Enter UserID to view, [c] to create new user, empty or [q] to quit','q');
    $output->writeln("");
    if(strtolower(sprintf("%s",$action)) === 'c')
      {
      return $this->CreateUser();
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
    $this->io->success('User successfully created!');
    return Command::SUCCESS;
    }

  /**
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

    return Command::FAILURE;
    }

  /**
   * Renders informations for given user object.
   * @param User $user
   */
  private function ShowUser(User $user)
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

  }
