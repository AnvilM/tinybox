<?php

declare(strict_types=1);

namespace App\Commands\Group;

use App\Application\Group\UseCase\GetGroupsList\GetGroupsListUseCase;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use League\CLImate\CLImate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'group:list', description: 'List groups', aliases: ['g:list'])]
final class ListSchemeGroupsCommand extends AbstractCommand
{
    public function __construct(
        ReporterPort                          $reporterPort,
        private readonly GetGroupsListUseCase $getGroupsListUseCase,
        ConfigInstancePort                    $configInstancePort,
    )
    {
        parent::__construct($reporterPort, $configInstancePort);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $schemeGroupNames = $this->getGroupsListUseCase->handle();


        new CLImate()->out('    Group name');
        foreach ($schemeGroupNames as $schemeGroupName) {
            new CLImate()->green()->out('[+] ' . $schemeGroupName);
        }

        return Command::SUCCESS;
    }
}