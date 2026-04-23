<?php

declare(strict_types=1);

namespace App\Application\Repository\Group\Shared\File;

use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\File\ReadJsonFileNotifyPort;

final readonly class ReadGroups
{
    public function __construct(
        private ReadJsonFileNotifyPort $readJsonFileNotifyPort,
        private ConfigInstancePort     $configInstancePort,
    )
    {
    }

    /**
     * Read group list from file
     *
     * @return array Unvalidated JSON decoded array of group
     *
     * @throws UnableToReadFileException Throws if unable to read file
     * @throws UnableToDecodeJsonException Throws if unable to parse JSON
     */
    public function read(): array
    {
        return $this->readJsonFileNotifyPort
            ->notifyStartAndSuccess(
                "Reading group list file...",
                "Group list file successfully read"
            )->read($this->configInstancePort->get()->groupsListPath);
    }
}