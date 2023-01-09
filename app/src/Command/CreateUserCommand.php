<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure(): void
    {
        $this->setName('create:users')
            ->setDescription('Command to create a user')
            ->addOption('firstname', 'f', InputOption::VALUE_REQUIRED, 'User firstname')
            ->addOption('lastname', 'l', InputOption::VALUE_REQUIRED, 'User lastname')
            ->addOption('email', 'm', InputOption::VALUE_REQUIRED, 'User email')
            ->addOption('username', 'u', InputOption::VALUE_REQUIRED, 'User username')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'User password')
            ->addOption('admin', 'a', InputOption::VALUE_NONE, 'Is user admin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $firstname = $input->getOption('firstname');
        $lastname = $input->getOption('lastname');
        $email = $input->getOption('email');
        $username = $input->getOption('username');
        $password = $input->getOption('password');
        $isAdmin = $input->getOption('admin') ?? false;

        $repository = $this->entityManager->getRepository(User::class);

        $user = $repository->findOneBy([
            'username' => $username,
            'email' => $email
        ]);

        $output->writeln(
            sprintf(
                '%s',
                $user ? '<info>User exists: Update user account</info>' : '<info>Creating a user account ...</info>'
            )
        );

        if(!$user){
            $user = new User();
            $user->setEmail($email);
            $user->setUsername($username);
        }

        $user
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setPassword($this->passwordHasher->hashPassword($user, $password))
            ->setRoles(
                $isAdmin ? [User::ADMIN_ROLE] : [User::USER_ROLE]
            );

        $this->entityManager->persist($user);
        $this->entityManager->flush();


        return Command::SUCCESS;
    }
}
