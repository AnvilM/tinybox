<?php

declare(strict_types=1);

namespace App\Application\Repository\SchemeGroup\Shared\File;

use App\Domain\SchemeGroup\Collection\SchemeGroupMap;
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
     * Write scheme groups list to file
     *
     * @throws UnableToEncodeJsonException If unable to convert scheme groups list to JSON
     * @throws UnableToSaveFileException If unable to save scheme groups list to file
     */
    public function write(SchemeGroupMap $schemeGroupsList): void
    {
        $path = $this->configInstancePort->get()->schemeGroupsListPath;

        $this->saveFileNotifyPort->notifyStartAndSuccess(
            "Saving scheme groups list...",
            "Scheme groups list successfully saved",
        )->save($path, $schemeGroupsList->toJson());


    }
}