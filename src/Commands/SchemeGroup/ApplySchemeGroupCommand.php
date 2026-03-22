<?php

declare(strict_types=1);

namespace App\Commands\SchemeGroup;

use App\Application\Services\SchemeGroup\ApplySchemeGroup\Handler\ApplySchemeGroupHandler;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'scheme-group:apply', description: 'Apply scheme group', aliases: ['sg:apply'])]
final class ApplySchemeGroupCommand extends AbstractCommand
{
    public function __construct(
        ReporterPort                             $reporterPort,
        private readonly ApplySchemeGroupHandler $applySchemeGroupHandler,
    )
    {
        parent::__construct($reporterPort);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $this->applySchemeGroupHandler->handle(
            new \App\Application\Services\SchemeGroup\ApplySchemeGroup\Command\ApplySchemeGroupCommand(
                $input->getArgument('name')
            )
        );

        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Subscription name')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug messages');
    }
}