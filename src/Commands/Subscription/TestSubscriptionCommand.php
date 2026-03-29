<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Application\Services\Subscription\TestSubscription\Handler\TestSubscriptionHandler;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use League\CLImate\CLImate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function Psl\Str\Grapheme\length;

#[AsCommand(name: 'subscription:test', description: 'Test subscription outbounds', aliases: ['sub:test'])]
final class TestSubscriptionCommand extends AbstractCommand
{
    public function __construct(
        ReporterPort                             $reporterPort,
        private readonly TestSubscriptionHandler $testSubscriptionHandler,
    )
    {
        parent::__construct($reporterPort);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {

        $result = $this->testSubscriptionHandler->handle(
            new \App\Application\Services\Subscription\TestSubscription\Command\TestSubscriptionCommand(
                $input->getArgument('name'),
                $input->getArgument('method'),
            )
        );

        $longestTagLength = 0;

        foreach ($result as $tag => $latency) {
            $tagLength = length($tag);
            if ($tagLength > $longestTagLength) {
                $longestTagLength = $tagLength;
            }
        }

        $cli = new CLImate();

        $cli->inline('     Tag');
        for ($i = 0; $i < $longestTagLength - length('Tag') + 2; $i++) {
            $cli->inline(' ');
        }
        $cli->inline('Latency');
        $cli->br();

        foreach ($result as $tag => $latency) {
            $cli->green()->inline('[+]  ');
            $cli->green()->inline($tag);

            $tagLength = length($tag);
            for ($i = 0; $i < $longestTagLength - $tagLength + 2; $i++) {
                $cli->inline(' ');
            }

            $cli->green()->inline($latency ?? 'N/A');
            $cli->br();
        }


        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Subscription name')
            ->addArgument('method', InputArgument::OPTIONAL, 'Test method e.g. proxy_get or tcp_ping. If not provided, will be used method form config')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug messages');
    }
}