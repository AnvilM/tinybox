<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Application\Services\Subscription\ListSubscriptions\Handler\ListSubscriptionsHandler;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use League\CLImate\CLImate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'subscription:list', description: 'List subscriptions', aliases: ['sub:list'])]
final class ListSubscriptionsCommand extends AbstractCommand
{

    public function __construct(
        ReporterPort                              $reporterPort,
        private readonly ListSubscriptionsHandler $listSubscriptionsHandler,
    )
    {
        parent::__construct($reporterPort);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $subscriptionsMap = $this->listSubscriptionsHandler->handle();

        $table = [];

        foreach ($subscriptionsMap as $name => $url) {
            $table[] = [
                'name' => $name,
                'url' => $url,
            ];
        }

        new CLImate()->table($table);

        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug messages');
    }
}