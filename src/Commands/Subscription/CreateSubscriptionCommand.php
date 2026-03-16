<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Application\Services\Subscription\CreateSubscription\Handler\CreateSubscriptionHandler;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'subscription:create', description: 'List schemes', aliases: ['sub:create'])]
final class CreateSubscriptionCommand extends AbstractCommand
{
    public function __construct(
        ReporterPort                               $reporterPort,
        private readonly CreateSubscriptionHandler $createSubscriptionHandler,
    )
    {
        parent::__construct($reporterPort);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $this->createSubscriptionHandler->handle(
            new \App\Application\Services\Subscription\CreateSubscription\Command\CreateSubscriptionCommand(
                $input->getArgument('name'),
                $input->getArgument('url'),
            )
        );

        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Subscription name')
            ->addArgument('url', InputArgument::REQUIRED, 'Subscription URL')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug messages');
    }
}