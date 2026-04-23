<?php

declare(strict_types=1);

namespace App\Application\Repository\Group\Shared\File;

use App\Domain\Group\Collection\GroupsMap;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\File\SaveFileNotifyPort;

final readonly class WriteGroups
{
    public function __construct(
        private SaveFileNotifyPort $saveFileNotifyPort,
        private ConfigInstancePort $configInstancePort,
    )
    {
    }

    /**
     * Write groups list to file
     *
     * @throws UnableToEncodeJsonException If unable to convert groups list to JSON
     * @throws UnableToSaveFileException If unable to save groups list to file
     */
    public function write(GroupsMap $schemeGroupsList): void
    {
        $path = $this->configInstancePort->get()->groupsListPath;

        $this->saveFileNotifyPort->notifyStartAndSuccess(
            "Saving groups list...",
            "Groups list successfully saved",
        )->save($path, $schemeGroupsList->toJson());


    }
}