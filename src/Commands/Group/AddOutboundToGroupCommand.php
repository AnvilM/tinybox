<?php

declare(strict_types=1);

namespace App\Commands\Group;

use App\Application\Group\UseCase\AddOutboundToGroup\AddOutboundToGroupUseCase;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'group:add', description: 'Add outbound to group or create new group with outbound', aliases: ['g:add'])]
final class AddOutboundToGroupCommand extends AbstractCommand
{
    public function __construct(
        ReporterPort                               $reporterPort,
        private readonly AddOutboundToGroupUseCase $addOutboundToGroupUseCase,
    )
    {
        parent::__construct($reporterPort);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $this->addOutboundToGroupUseCase->handle(
            $input->getArgument('groupName'),
            (int)$input->getArgument('outboundId')
        );

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('groupName', InputArgument::REQUIRED, 'Group name')
            ->addArgument('outboundId', InputArgument::REQUIRED, 'Outbound id')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug messages');
    }
}