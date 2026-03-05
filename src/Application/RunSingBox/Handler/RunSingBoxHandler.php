<?php

declare(strict_types=1);

namespace App\Application\RunSingBox\Handler;

use App\Application\RunSingBox\Command\RunSingBoxCommand;
use App\Application\RunSingBox\File\CopyConfigToDefaultSingBoxConfig;
use App\Application\RunSingBox\Process\RestartSingBoxSystemd;
use App\Application\RunSingBox\Process\RunSingBox;
use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Ports\Config\ConfigInstancePort;
use App\Core\Shared\Ports\IO\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\RunSingBox\File\CopyConfigToDefaultSingBoxConfig\ConfigSuccessfullyCopiedReporterEvent;
use RuntimeException;

final readonly class RunSingBoxHandler
{
    public function __construct(
        private RunSingBox                       $runSingBox,
        private ConfigInstancePort               $configInstancePort,
        private CopyConfigToDefaultSingBoxConfig $copyConfigToDefaultSingBoxConfig,
        private RestartSingBoxSystemd            $restartSingBoxSystemd,
        private ReporterPort                     $reporterPort,
    )
    {
    }

    public function handle(RunSingBoxCommand $command): void
    {
        $configPath = $this->configInstancePort->get()->generatedConfigsDirectoryPath . "/$command->subscriptionName.json";

        if (!file_exists($configPath))
            throw new CriticalException("Config $command->subscriptionName doesn't exist", $configPath);

        if (!$command->isSystemCtl)
            try {
                $this->runSingBox->run($configPath);
                return;
            } catch (RuntimeException) {
                throw new CriticalException('Unable to run sing-box');
            }


        try {
            $this->copyConfigToDefaultSingBoxConfig->copy($configPath);
        } catch (RuntimeException $e) {
            throw new CriticalException("Unable to copy config", $e->getMessage());
        }

        $this->reporterPort->notify(new ConfigSuccessfullyCopiedReporterEvent(
            $configPath,
            $this->configInstancePort->get()->singBoxConfig->defaultConfigPath,
            $command->subscriptionName
        ));

        try {
            $this->restartSingBoxSystemd->reload($command->subscriptionName);
        } catch (RuntimeException $e) {
            throw new CriticalException("Unable to reload sing-box service", $e->getMessage());
        }
    }
}