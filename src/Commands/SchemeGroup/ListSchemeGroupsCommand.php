<?php

declare(strict_types=1);

namespace App\Commands\SchemeGroup;

use App\Application\Services\SchemeGroup\ListSchemeGroups\Handler\ListSchemeGroupsHandler;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use League\CLImate\CLImate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'scheme-group:list', description: 'List scheme groups', aliases: ['sg:list'])]
final class ListSchemeGroupsCommand extends AbstractCommand
{
    public function __construct(
        ReporterPort                             $reporterPort,
        private readonly ListSchemeGroupsHandler $listSchemeGroupsHandler,
    )
    {
        parent::__construct($reporterPort);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $schemeGroupNames = $this->listSchemeGroupsHandler->handle();


        new CLImate()->out('    SchemeGroup name');
        foreach ($schemeGroupNames as $schemeGroupName) {
            new CLImate()->green()->out('[+] ' . $schemeGroupName);
        }

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug messages');
    }
}