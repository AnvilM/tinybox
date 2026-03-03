<?php

declare(strict_types=1);

namespace App\Commands;

use App\Application\ScanGeneratedConfigsDirectory\Handler\ScanGeneratedConfigsDirectoryHandler;
use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Ports\IO\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\Shared\FatalErrorReporterEvent;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use League\CLImate\CLImate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(name: 'config:list', description: 'List all generated configs')]
final readonly class ListConfigsCommand
{
    public function __construct(
        private ScanGeneratedConfigsDirectoryHandler $scanGeneratedConfigsDirectoryHandler,
        private ReporterPort                         $reporterPort,
    )
    {
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        try {
            $configNames = $this->scanGeneratedConfigsDirectoryHandler->handle()->configsNames;

            if (empty($configNames)) {
                new CLImate()->green()->out("No one configuration files have been found");
                return Command::SUCCESS;
            }

            foreach ($configNames as $configName) {
                new CLImate()->green()->out("[+] $configName");
            }

        } catch (CriticalException $e) {
            $this->reporterPort->notify(new FatalErrorReporterEvent(
                $e->getMessage(),
                $e->debugMessage ? DebugMessagesVO::create([$e->debugMessage]) : null
            ));

            return Command::FAILURE;
        } catch (Throwable $e) {
            $this->reporterPort->notify(new FatalErrorReporterEvent(
                "Unhandled exception",
                DebugMessagesVO::create([$e->getMessage()])
            ));
        }

        return Command::SUCCESS;
    }
}