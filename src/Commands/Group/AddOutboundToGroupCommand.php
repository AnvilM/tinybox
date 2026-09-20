<?php

declare(strict_types=1);

namespace App\Commands\Group;

use App\Application\Group\UseCase\AddOutboundToGroup\AddOutboundToGroupUseCase;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterInstancePort;
use Iva\ExitCode;
use Iva\Input\Argument;
use Iva\Input\Input;
use Iva\Output\Output;

final class AddOutboundToGroupCommand extends AbstractCommand
{
    private Argument $groupNameArgument;
    private Argument $outboundIdArgument;

    public function __construct(
        ReporterInstancePort                       $reporterInstancePort,
        private readonly AddOutboundToGroupUseCase $addOutboundToGroupUseCase,
        ConfigInstancePort                         $configInstancePort,
    )
    {
        parent::__construct($reporterInstancePort, $configInstancePort);
    }

    protected function configureCommand(): void
    {
        $this->setName('add');
        $this->setDescription('Add outbound to group or create new group with outbound');

        $this->groupNameArgument = $this->addArgument(Argument::string('groupName', 'Group name'));
        // Iva coerces this to a real int for us — no more (int) casting in handle().
        $this->outboundIdArgument = $this->addArgument(Argument::int('outboundId', 'Outbound id'));
    }

    protected function handle(Input $input, Output $output): int
    {
        $this->addOutboundToGroupUseCase->handle(
            $input->argument($this->groupNameArgument),
            $input->argument($this->outboundIdArgument),
        );

        return ExitCode::Ok->value;
    }
}
