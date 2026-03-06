<?php

declare(strict_types=1);

namespace App\Commands;

use App\Application\GenerateConfigs\Command\GenerateConfigsCommand;
use App\Application\GenerateConfigs\Handler\GenerateConfigsHandler;
use App\Application\RunSingBox\Command\RunSingBoxCommand;
use App\Application\RunSingBox\Handler\RunSingBoxHandler;
use App\Application\ScanGeneratedConfigsDirectory\Handler\ScanGeneratedConfigsDirectoryHandler;
use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Ports\IO\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\Shared\FatalErrorReporterEvent;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(name: 'config:create', description: 'Create config from raw scheme string')]
final class CreateConfigCommand extends Command
{
    public function __construct(
        private readonly ReporterPort                         $reporterPort,
        private readonly ScanGeneratedConfigsDirectoryHandler $scanGeneratedConfigsDirectoryHandler,
        private readonly GenerateConfigsHandler               $generateConfigsHandler,
        private readonly RunSingBoxHandler                    $runSingBoxHandler,
    )
    {
        parent::__construct();
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        try {
            $existingConfigsNames = $this->scanGeneratedConfigsDirectoryHandler->handle()->configsNames;

            if (in_array($input->getArgument('configName'), $existingConfigsNames) && !$input->getOption('force'))
                throw new CriticalException("Config {$input->getArgument('configName')} already exists. Use --force to overwrite it.");

            $this->generateConfigsHandler->handle(new GenerateConfigsCommand([
                $input->getArgument('configName') => $input->getArgument('schemeString')
            ]));

            if (!$input->getOption('apply')) return Command::SUCCESS;

            $this->runSingBoxHandler->handle(
                new RunSingBoxCommand(
                    $input->getArgument('configName'),
                    (bool)$input->getOption('systemd'),
                )
            );

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

    protected function configure(): void
    {
        $this->addArgument("configName", InputArgument::REQUIRED, "The config name")
            ->addArgument("schemeString", InputArgument::REQUIRED, "The config scheme string")
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force overwrite existing config')
            ->addOption('apply', 'a', InputOption::VALUE_NONE, 'Apply created config')
            ->addOption('systemd', 's', InputOption::VALUE_NONE, 'Apply config using systemctl sing-box service')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug messages');
    }
}