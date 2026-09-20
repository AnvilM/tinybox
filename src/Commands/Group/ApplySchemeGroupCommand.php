<?php

declare(strict_types=1);

namespace App\Commands\Group;

use App\Application\Group\UseCase\ApplyGroup\ApplyGroupUseCase;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterInstancePort;
use Iva\ExitCode;
use Iva\Input\Argument;
use Iva\Input\Input;
use Iva\Output\Output;

final class ApplySchemeGroupCommand extends AbstractCommand
{
    private Argument $groupNameArgument;

    public function __construct(
        ReporterInstancePort               $reporterInstancePort,
        private readonly ApplyGroupUseCase $applyGroupUseCase,
        ConfigInstancePort                 $configInstancePort,
    )
    {
        parent::__construct($reporterInstancePort, $configInstancePort);
    }

    protected function configureCommand(): void
    {
        $this->setName('apply');
        $this->setDescription('Apply group');

        $this->groupNameArgument = $this->addArgument(Argument::string('groupName', 'Group name'));
    }

    protected function handle(Input $input, Output $output): int
    {
        $this->applyGroupUseCase->handle(
            $input->argument($this->groupNameArgument)
        );

        return ExitCode::Ok->value;
    }
}
