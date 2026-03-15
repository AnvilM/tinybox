<?php

declare(strict_types=1);

namespace App\Application\Shared\Config\Shared\File;

use App\Domain\Config\Collection\ConfigMap;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\File\SaveFileNotifyPort;

final readonly class WriteConfigs
{
    public function __construct(
        private SaveFileNotifyPort $saveFileNotifyPort,
        private ConfigInstancePort $configInstancePort,
    )
    {
    }

    /**
     * @throws CriticalException
     */
    public function write(ConfigMap $configMap): void
    {
        $path = $this->configInstancePort->get()->configsListPath;

        try {
            $this->saveFileNotifyPort->notifyStartAndSuccess(
                "Saving configs...",
                "Configs successfully saved",
            )->save($path, $configMap->toJson());
        } catch (UnableToSaveFileException|UnableToEncodeJsonException) {
            throw new CriticalException("Unable to save configs", $path);
        }


    }
}