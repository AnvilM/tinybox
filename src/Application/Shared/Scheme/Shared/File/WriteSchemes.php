<?php

declare(strict_types=1);

namespace App\Application\Shared\Scheme\Shared\File;

use App\Domain\Scheme\Collection\SchemeMap;
use App\Domain\Shared\Exception\CriticalException;
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
     * @throws CriticalException
     */
    public function write(SchemeMap $schemeMap): void
    {
        $path = $this->configInstancePort->get()->schemesListPath;

        try {
            $this->saveFileNotifyPort->notifyStartAndSuccess(
                "Saving schemes...",
                "Schemes successfully saved",
            )->save($path, $schemeMap->toJson());
        } catch (UnableToSaveFileException|UnableToEncodeJsonException) {
            throw new CriticalException("Unable to save schemes", $path);
        }


    }
}