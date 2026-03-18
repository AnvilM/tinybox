<?php

declare(strict_types=1);

namespace App\Application\Shared\Scheme\Shared\File;

use App\Domain\Scheme\Collection\SchemeMap;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\File\SaveFileNotifyPort;

final readonly class WriteSchemes
{
    public function __construct(
        private SaveFileNotifyPort $saveFileNotifyPort,
        private ConfigInstancePort $configInstancePort,
    )
    {
    }

    /**
     * Write schemes map to schemes file
     *
     * @throws UnableToEncodeJsonException If unable to convert schemes map to JSON
     * @throws UnableToSaveFileException If unable to save schemes to file
     */
    public function write(SchemeMap $schemeMap): void
    {
        $path = $this->configInstancePort->get()->schemesListPath;

        $this->saveFileNotifyPort->notifyStartAndSuccess(
            "Saving schemes...",
            "Schemes successfully saved",
        )->save($path, $schemeMap->toJson());


    }
}