<?php

declare(strict_types=1);

namespace App\Commands\Config;

use App\Application\Services\Config\ApplyConfig\Handler\ApplyConfigHandler;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'config:apply', description: 'Apply config', aliases: ['cf:apply'])]
final class ApplyConfigCommand extends AbstractCommand
{
    public function __construct(
        ReporterPort                        $reporterPort,
        private readonly ApplyConfigHandler $applyConfigHandler,
    )
    {
        parent::__construct($reporterPort);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $this->applyConfigHandler->handle(
            new \App\Application\Services\Config\ApplyConfig\Command\ApplyConfigCommand(
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