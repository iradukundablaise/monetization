<?php

namespace App\Command;

use App\Entity\User;
use App\Service\Yegob_WP_Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'import:users',
    description: 'Add a short description for your command',
)]
class ImportUsersCommand extends Command
{
    const DEFAULT_PWD = '@PasswordYegoB123';
    private Yegob_WP_Service $yegobService;
    private UserPasswordHasherInterface $hasher;
    private EntityManagerInterface $entityManager;

    public function __construct(
        Yegob_WP_Service $yegobService,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
        $this->yegobService = $yegobService;
        $this->hasher = $hasher;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $page = 1;
        $repository = $this->entityManager->getRepository(User::class);

        $users = $this->yegobService->getUsersFromWP();
        dd($users);
        while($users != []){
            // create users //
            foreach($users as $user){
                $userExists = $repository->findOneBy([
                    'username' => $user['nicename'],
                    'email' => $user['email']
                ]);

                if(!$userExists){
                    $newUser = (new User())
                        ->setFirstname($user['firstname'])
                        ->setLastname($user['lastname'])
                        ->setEmail($user['email'])
                        ->setUsername($user['nicename']);

                    $newUser->setPassword(
                        $this->hasher->hashPassword($newUser, self::DEFAULT_PWD)
                    );
                    $this->entityManager->persist($newUser);
                    $output->writeln(
                        sprintf("<info>%s 's account </info>will be created", $user['firstname'])
                    );
                }else{
                    $output->writeln(
                        sprintf(
                            "<error>Error</error>: %s's account exist already and will not be created",
                            $user['firstname']
                        )
                    );
                }
            }
            $page += 1;
            $users = $this->yegobService->getUsersFromWP($page);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
