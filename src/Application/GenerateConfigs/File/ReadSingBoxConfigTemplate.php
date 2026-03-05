<?php

declare(strict_types=1);

namespace App\Application\GenerateConfigs\File;

use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Exception\File\UnableToDecodeJSONException;
use App\Core\Shared\Exception\File\UnableToReadFileException;
use App\Core\Shared\Ports\Config\ConfigInstancePort;
use App\Core\Shared\Ports\IO\File\ReadJsonFileNotifyPort;

final readonly class ReadSingBoxConfigTemplate
{
    public function __construct(
        private ReadJsonFileNotifyPort $readJsonFileNotifyPort,
        private ConfigInstancePort     $configInstancePort,
    )
    {
    }

    /**
     * Reads sing-box config template from file
     *
     * @return array Sing-box config template as json decoded array
     *
     * @throws CriticalException If unable to read file, parse json or format is invalid
     */
    public function read(): array
    {
        try {
            return $this->readJsonFileNotifyPort
                ->notifyStartAndSuccess(
                    "Reading sing-box config template file...",
                    "Sing-box config template file successfully read"
                )->read($this->configInstancePort->get()->singBoxConfig->templates->config);
        } catch (UnableToDecodeJSONException|UnableToReadFileException $e) {
            throw new CriticalException(
                ($e instanceof UnableToDecodeJSONException)
                    ? "Unable to parse JSON from sing-box config template file"
                    : "Unable to read sing-box config template file",
                $this->configInstancePort->get()->singBoxConfig->templates->config
            );
        }
    }
}