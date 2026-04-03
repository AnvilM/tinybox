<?php

declare(strict_types=1);

namespace App\Application\Shared\UseCase\SaveSingBoxConfig;

use App\Application\Shared\DTO\UseCase\SaveSingBoxConfig\SaveSingBoxConfigDTO;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\File\SaveFilePort;

final readonly class SaveSingBoxConfigUseCase
{
    public function __construct(
        private SaveFilePort       $saveFilePort,
        private ConfigInstancePort $configInstancePort,
    )
    {
    }

    /**
     * Save JSON string sing-box config to file
     *
     * @throws CriticalException
     */
    public function handle(SaveSingBoxConfigDTO $dto): void
    {
        /**
         * Try to save config file
         */
        try {
            $this->saveFilePort->save(
                $this->configInstancePort->get()->singBoxConfig->defaultConfigPath,
                $dto->singBoxConfig
            );
        } catch (UnableToSaveFileException) {
            throw new CriticalException("Unable to save the configuration file");
        }
    }
}