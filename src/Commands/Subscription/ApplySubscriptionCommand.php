<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Application\Services\Subscription\ApplySubscription\Handler\ApplySubscriptionHandler;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'subscription:apply', description: 'Apply subscription', aliases: ['sub:apply'])]
final class ApplySubscriptionCommand extends AbstractCommand
{
    public function __construct(
        ReporterPort                              $reporterPort,
        private readonly ApplySubscriptionHandler $applySubscriptionHandler,
    )
    {
        parent::__construct($reporterPort);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $this->applySubscriptionHandler->handle(
            new \App\Application\Services\Subscription\ApplySubscription\Command\ApplySubscriptionCommand(
                $input->getArgument('name'),
                !($input->getOption('systemd') === null),
            )
        );

        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Subscription name')
            ->addOption('systemd', 's', InputOption::VALUE_NONE, 'If true config will be saved as systemd sing-box config and sing-box systemd service will be restarted')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug messages');
    }
}