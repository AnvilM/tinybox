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
use function Psl\Str\length;

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

        $longestNameLength = 0;
        $longestUrlLength = 0;

        foreach ($subscriptionsMap as $subscriptionName => $subscriptionUrl) {
            if (length($subscriptionName) > $longestNameLength) $longestNameLength = length($subscriptionName);
            if (length($subscriptionUrl) > $longestUrlLength) $longestUrlLength = length($subscriptionUrl);
        }

        new CLImate()->inline('     ');
        new CLImate()->inline('Name');
        for ($i = 0; $i < $longestNameLength; ++$i) {
            new CLImate()->inline(' ');
        }
        new CLImate()->inline('Url');
        new CLImate()->br();


        foreach ($subscriptionsMap as $subscriptionName => $subscriptionUrl) {

            new CLImate()->green()->inline('[+]');
            new CLImate()->inline('  ');
            $nameLength = length($subscriptionName);
            new CLImate()->green()->inline($subscriptionName);
            for ($i = 0; $i < $longestNameLength - $nameLength + 4; ++$i) {
                new CLImate()->inline(' ');
            }
            new CLImate()->green()->inline($subscriptionUrl);


            new CLImate()->br();

        }

        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug messages');
    }
}