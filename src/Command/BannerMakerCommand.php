<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\BannerMaker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BannerMakerCommand extends Command
{
    protected static $defaultName = 'banner:make';
    public $repository;

    public function __construct(private readonly BannerMaker $bannerMakerService, private readonly EntityManagerInterface $em, private readonly \App\Repository\UserRepository $userRepository)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Generate banners for users')
            ->addArgument('user', InputArgument::OPTIONAL, 'User ID');
    }

    /**
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $users = [];
        $output->writeln('Start making banners.');

        $userId = $input->getArgument('user');

        if (null !== $userId) {
            $users[] = $this->repository->findOneBy(['id' => $userId]);
        } else {
            $users = $this->repository->findAll();
        }

        foreach ($users as $user) {
            $this->bannerMakerService->makeBanner($user);
        }

        $output->writeln('End.');
    }
}
