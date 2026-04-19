<?php

declare(strict_types=1);

namespace App\Commands\Scheme;

use App\Application\Scheme\UseCase\GetSchemesList\GetSchemesListUseCase;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'scheme:toOutbound', description: 'Convert schemes to outbounds', aliases: ['sc:toOutbound'])]
final class ToOutboundsCommand extends AbstractCommand
{
    public function __construct(
        ReporterPort                           $reporterPort,
        private readonly GetSchemesListUseCase $getSchemesListUseCase,
    )
    {
        parent::__construct($reporterPort);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $this->getSchemesListUseCase->handle();

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug messages');
    }


}