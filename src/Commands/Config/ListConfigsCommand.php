<?php

declare(strict_types=1);

namespace App\Commands\Config;

use App\Application\ListConfigs\Handler\ListConfigsHandler;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use League\CLImate\CLImate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'config:list', description: 'List configs')]
final class ListConfigsCommand extends AbstractCommand
{
    public function __construct(
        ReporterPort                        $reporterPort,
        private readonly ListConfigsHandler $listConfigsHandler,
    )
    {
        parent::__construct($reporterPort);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $configNames = $this->listConfigsHandler->handle();


        new CLImate()->out('    Config name');
        foreach ($configNames as $configName) {
            new CLImate()->green()->out('[+] ' . $configName);
        }

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug messages');
    }
}