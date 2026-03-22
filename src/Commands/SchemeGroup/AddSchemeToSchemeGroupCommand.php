<?php

declare(strict_types=1);

namespace App\Commands\SchemeGroup;

use App\Application\Services\SchemeGroup\AddSchemeToSchemeGroup\Handler\AddSchemeToSchemeGroupHandler;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'scheme-group:add', description: 'Add scheme to scheme group or create new scheme group with scheme', aliases: ['sg:add'])]
final class AddSchemeToSchemeGroupCommand extends AbstractCommand
{
    public function __construct(
        ReporterPort                                   $reporterPort,
        private readonly AddSchemeToSchemeGroupHandler $addSchemeToSchemeGroupHandler
    )
    {
        parent::__construct($reporterPort);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $this->addSchemeToSchemeGroupHandler->handle(
            new \App\Application\Services\SchemeGroup\AddSchemeToSchemeGroup\Command\AddSchemeToSchemeGroupCommand(
                $input->getArgument('schemeGroupName'),
                $input->getArgument('schemeId')
            )
        );

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('schemeGroupName', InputArgument::REQUIRED, 'SchemeGroup name')
            ->addArgument('schemeId', InputArgument::REQUIRED, 'Scheme id')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug messages');
    }
}