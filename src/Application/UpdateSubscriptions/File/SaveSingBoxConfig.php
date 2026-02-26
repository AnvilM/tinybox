<?php

declare(strict_types=1);

namespace App\Application\UpdateSubscriptions\File;

use App\Core\Shared\Ports\Config\ConfigFactoryPort;
use App\Core\Shared\Ports\File\SaveFilePort;
use App\Core\Shared\Ports\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\UpdateSubscriptionsLifecycle\File\SaveSingBoxConfig\SingBoxConfigSavingSuccessfullyReporterEvent;
use App\Core\Shared\ReporterEvent\Events\UpdateSubscriptionsLifecycle\File\SaveSingBoxConfig\StartSavingSingBoxConfigFileReporterEvent;

final readonly class SaveSingBoxConfig
{
    public function __construct(
        private SaveFilePort      $saveFilePort,
        private ConfigFactoryPort $configFactoryPort,
        private ReporterPort      $reporterPort,
    )
    {
    }

    public function save(string $SingBoxConfigJSON, string $subscriptionName): void
    {
        $path = $this->configFactoryPort->get()->generatedConfigsDirectoryPath . "/$subscriptionName.json";

        $this->reporterPort->notify(new StartSavingSingBoxConfigFileReporterEvent(
            $subscriptionName, $path
        ));

        $this->saveFilePort->save(
            $path,
            $SingBoxConfigJSON
        );

        $this->reporterPort->notify(new SingBoxConfigSavingSuccessfullyReporterEvent(
            $subscriptionName, $path
        ));
    }
}