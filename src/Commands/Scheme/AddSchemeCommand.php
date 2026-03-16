<?php

declare(strict_types=1);

namespace App\Commands\Scheme;

use App\Application\Services\Scheme\AddScheme\Handler\AddSchemeHandler;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use League\CLImate\CLImate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'scheme:add', description: 'Add scheme')]
final class AddSchemeCommand extends AbstractCommand
{
    public function __construct(
        ReporterPort                      $reporterPort,
        private readonly AddSchemeHandler $addSchemeHandler,
    )
    {
        parent::__construct($reporterPort);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        new CLImate()->out(
            $this->addSchemeHandler->handle(
                new \App\Application\Services\Scheme\AddScheme\Command\AddSchemeCommand(
                    $input->getArgument('scheme'),
                )
            )
        );

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('scheme', InputArgument::REQUIRED, 'Scheme string')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug messages');
    }


}