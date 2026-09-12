<?php

declare(strict_types=1);

namespace App\Application\Shared\UseCase\CreateConfig\FIle;

use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\File\ReadJsonFileNotifyPort;

final readonly class ReadBalancerTemplate
{
    public function __construct(
        private ReadJsonFileNotifyPort $readJsonFileNotifyPort,
        private ConfigInstancePort     $configInstancePort
    )
    {
    }


    /**
     * Read xray balancer template file
     *
     * @return array Xray balancer template as JSON decoded array
     *
     * @throws UnableToReadFileException If unable to read file
     * @throws UnableToDecodeJsonException If unable to decode JSON
     *
     */
    public function read(): array
    {
        return $this->readJsonFileNotifyPort->notifyStartAndSuccess(
            "Reading xray balancer template file...",
            "Xray balancer template file successfully read"
        )->read($this->configInstancePort->get()->xrayConfig->templates->balancer);
    }
}