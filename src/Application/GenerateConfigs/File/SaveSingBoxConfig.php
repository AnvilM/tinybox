<?php

declare(strict_types=1);

namespace App\Application\GenerateConfigs\File;

use App\Core\Shared\Ports\Config\ConfigInstancePort;
use App\Core\Shared\Ports\IO\File\SaveFilePort;
use App\Core\Shared\Ports\IO\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\GenerateConfigs\File\SaveSingBoxConfig\SingBoxConfigSavingSuccessfullyReporterEvent;
use App\Core\Shared\ReporterEvent\Events\GenerateConfigs\File\SaveSingBoxConfig\StartSavingSingBoxConfigFileReporterEvent;

final readonly class SaveSingBoxConfig
{
    public function __construct(
        private SaveFilePort       $saveFilePort,
        private ConfigInstancePort $configInstancePort,
        private ReporterPort       $reporterPort,
    )
    {
    }

    public function save(string $SingBoxConfigJSON, string $subscriptionName): void
    {
        $path = $this->configInstancePort->get()->generatedConfigsDirectoryPath . "/$subscriptionName.json";

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