<?php

namespace App\Command;

use App\Entity\Report;
use App\Entity\User;
use App\Service\GoogleAnalyticsService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class FetchGoogleReportsCommand extends Command
{
    private GoogleAnalyticsService $analyticsService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        GoogleAnalyticsService $analyticsService,
        EntityManagerInterface $entityManager
    )
    {
        parent::__construct();

        $this->analyticsService = $analyticsService;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->setName('reports:fetch')
            ->setDescription('Add a short description for your command')
            ->addOption('startDate', null, InputOption::VALUE_OPTIONAL, 'Start Date')
            ->addOption('endDate', null, InputOption::VALUE_OPTIONAL, 'End Date');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $today = Carbon::today()->format('Y-m-d');
        $startDate = $input->getOption('startDate') ?? $today;
        $endDate = $input->getOption('endDate') ?? $today;

        $reports = $this->analyticsService->getReportsByAuthor($startDate, $endDate);

        $userRepository = $this->entityManager->getRepository(User::class);
        $reportRepository = $this->entityManager->getRepository(Report::class);

        foreach($reports as $author => $report){
            $userExists = $userRepository->findOneBy(['username' => $author ]);

            if($userExists){
                foreach ($report as $date => $views){
                    $date = Carbon::createFromFormat('Ymd', $date);
                    $newReport = $reportRepository->findOneBy([
                        'created_at' => $date,
                        'user' => $userExists
                    ]);

                    if(!$newReport){
                        $newReport = (new Report())
                            ->setUser($userExists)
                            ->setCreatedAt($date);
                    }else{
                        $output->writeln('Report exists already');
                    }

                    $newReport
                        ->setPageviews($views['pageviews'])
                        ->setUniquePageviews($views['uniquePageviews'])
                        ->setUpdatedAt($date);

                    $this->entityManager->persist($newReport);
                }
            }

        }
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
