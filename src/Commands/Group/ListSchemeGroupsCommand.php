<?php

declare(strict_types=1);

namespace App\Commands\Group;

use App\Application\Group\UseCase\GetGroupsList\GetGroupsListUseCase;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterInstancePort;
use Iva\ExitCode;
use Iva\Input\Input;
use Iva\Output\Output;

final class ListSchemeGroupsCommand extends AbstractCommand
{
    public function __construct(
        ReporterInstancePort                  $reporterInstancePort,
        private readonly GetGroupsListUseCase $getGroupsListUseCase,
        ConfigInstancePort                    $configInstancePort,
    )
    {
        parent::__construct($reporterInstancePort, $configInstancePort);
    }

    protected function configureCommand(): void
    {
        $this->setName('list');
        $this->setDescription('List groups');
    }

    protected function handle(Input $input, Output $output): int
    {
        $schemeGroupNames = $this->getGroupsListUseCase->handle();

        // Ported from League\CLImate: out('...') -> writeln(), green()->out(...) -> the built-in <success> style.
        $output->writeln('    Group name');

        foreach ($schemeGroupNames as $schemeGroupName) {
            $output->writeln(sprintf('<success>[+] %s</success>', $schemeGroupName));
        }

        return ExitCode::Ok->value;
    }
}
