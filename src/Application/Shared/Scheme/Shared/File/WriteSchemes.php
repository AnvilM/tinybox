<?php

declare(strict_types=1);

namespace App\Application\Shared\Scheme\Shared\File;

use App\Domain\Scheme\Collection\SchemeCollection;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
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
    public function write(SchemeCollection $schemeCollection): void
    {
        $path = $this->configInstancePort->get()->schemesListPath;

        try {
            $this->saveFileNotifyPort->notifyStartAndSuccess(
                "Saving schemes...",
                "Schemes successfully saved",
            )->save($path, $schemeCollection->toJson());
        } catch (UnableToSaveFileException) {
            throw new CriticalException("Unable to save schemes", $path);
        }


    }
}