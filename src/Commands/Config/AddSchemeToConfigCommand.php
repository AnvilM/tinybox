<?php

declare(strict_types=1);

namespace App\Commands\Config;

use App\Application\AddSchemeToConfig\Handler\AddSchemeToConfigHandler;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'config:add-scheme', description: 'Add scheme to config or create new config with scheme')]
final class AddSchemeToConfigCommand extends AbstractCommand
{
    public function __construct(
        ReporterPort                              $reporterPort,
        private readonly AddSchemeToConfigHandler $addSchemeToConfigHandler
    )
    {
        parent::__construct($reporterPort);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $this->addSchemeToConfigHandler->handle(
            new \App\Application\AddSchemeToConfig\Command\AddSchemeToConfigCommand(
                $input->getArgument('configName'),
                $input->getArgument('schemeId')
            )
        );

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('configName', InputArgument::REQUIRED, 'Config name')
            ->addArgument('schemeId', InputArgument::REQUIRED, 'Scheme id')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug messages');
    }
}