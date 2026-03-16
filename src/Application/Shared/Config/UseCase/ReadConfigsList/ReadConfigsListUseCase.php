<?php

declare(strict_types=1);

namespace App\Application\Shared\Config\UseCase\ReadConfigsList;

use App\Application\Shared\Config\Exception\Shared\Validator\InvalidConfigsListFormatException;
use App\Application\Shared\Config\Shared\File\ReadConfigs;
use App\Application\Shared\Config\Shared\Validator\ConfigsListFormatValidator;
use App\Application\Shared\Scheme\UseCase\ReadSchemesList\ReadSchemesListUseCase;
use App\Domain\Config\Collection\ConfigMap;
use App\Domain\Config\Entity\Config;
use App\Domain\Config\Exception\ConfigAlreadyExistsException;
use App\Domain\Config\Exception\InvalidConfigNameException;
use App\Domain\Config\VO\ConfigNameVO;
use App\Domain\Scheme\Collection\UniqueSchemesMap;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use App\Domain\Scheme\Exception\SchemeNotFoundException;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;

final readonly class ReadConfigsListUseCase
{
    public function __construct(
        private ReadConfigs                $readConfigs,
        private ConfigsListFormatValidator $configsListFormatValidator,
        private ReadSchemesListUseCase     $readSchemesListUseCase,
    )
    {
    }

    /**
     * @throws CriticalException
     */
    public function handle(): ConfigMap
    {
        try {
            $rawConfigsListArray = $this->readConfigs->read();

            $this->configsListFormatValidator->validate($rawConfigsListArray);


            /** @var array<array{name: string, schemes: string[]}> $rawConfigsListArray */

        } catch (UnableToReadFileException $e) {
            throw new CriticalException("Unable to read configs list", $e->getMessage());
        } catch (UnableToDecodeJsonException|InvalidConfigsListFormatException $e) {
            throw new CriticalException("Invalid configs list format", $e->getMessage());
        }


        $schemes = $this->readSchemesListUseCase->handle();
        $configs = new ConfigMap();

        foreach ($rawConfigsListArray as $rawConfig) {
            $configSchemes = new UniqueSchemesMap();

            foreach ($rawConfig['schemes'] as $rawConfigScheme) {
                try {
                    $scheme = $schemes->getById($rawConfigScheme);
                } catch (SchemeNotFoundException) {
                    continue;
                    //TODO: Add reporter event
                }

                try {
                    $configSchemes->add($scheme);
                } catch (SchemeAlreadyExistsException) {
                    continue;
                    //TODO: Add reporter event
                }

            }

            try {
                $configs->add(
                    new Config(new ConfigNameVO($rawConfig['name']), $configSchemes)
                );
            } catch (ConfigAlreadyExistsException|InvalidConfigNameException) {
                continue;
                //TODO: Add reporter event
            }

        }

        return $configs;
    }
}