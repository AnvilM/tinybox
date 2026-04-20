<?php

declare(strict_types=1);

namespace App\Commands\Group;

use App\Application\Group\UseCase\ApplyGroup\ApplyGroupUseCase;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'group:apply', description: 'Apply group', aliases: ['g:apply'])]
final class ApplySchemeGroupCommand extends AbstractCommand
{
    public function __construct(
        ReporterPort                       $reporterPort,
        private readonly ApplyGroupUseCase $applyGroupUseCase,
    )
    {
        parent::__construct($reporterPort);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $this->applyGroupUseCase->handle(
            $input->getArgument('groupName')
        );

        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('groupName', InputArgument::REQUIRED, 'Group name')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug messages');
    }
}