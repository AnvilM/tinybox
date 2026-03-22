<?php

declare(strict_types=1);

namespace App\Application\Shared\SchemeGroup\Shared\File;

use App\Domain\SchemeGroup\Collection\SchemeGroupMap;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\File\SaveFileNotifyPort;

final readonly class WriteSchemeGroups
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
    public function write(SchemeGroupMap $configMap): void
    {
        $path = $this->configInstancePort->get()->schemeGroupsListPath;

        try {
            $this->saveFileNotifyPort->notifyStartAndSuccess(
                "Saving scheme groups list...",
                "Scheme groups list successfully saved",
            )->save($path, $configMap->toJson());
        } catch (UnableToSaveFileException|UnableToEncodeJsonException) {
            throw new CriticalException("Unable to save scheme groups", $path);
        }


    }
}