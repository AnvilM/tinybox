<?php

declare(strict_types=1);

namespace App\Application\Shared\SchemeGroup\Shared\File;

use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\File\ReadJsonFileNotifyPort;

final readonly class ReadSchemeGroups
{
    public function __construct(
        private ReadJsonFileNotifyPort $readJsonFileNotifyPort,
        private ConfigInstancePort     $configInstancePort,
    )
    {
    }

    /**
     * Read scheme group list from file
     *
     * @return array Unvalidated JSON decoded array of scheme group
     *
     * @throws UnableToReadFileException Throws if unable to read file
     * @throws UnableToDecodeJsonException Throws if unable to parse JSON
     */
    public function read(): array
    {
        return $this->readJsonFileNotifyPort
            ->notifyStartAndSuccess(
                "Reading scheme group list file...",
                "Scheme group list file successfully read"
            )->read($this->configInstancePort->get()->schemeGroupsListPath);
    }
}