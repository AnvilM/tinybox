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
     * Read configs list from file
     *
     * @return ConfigMap Map of configs entity
     *
     * @throws CriticalException
     */
    public function handle(): ConfigMap
    {
        try {
            /**
             * Read configs
             */
            $rawConfigsList = $this->readConfigs->read();


            /**
             * Validate configs
             */
            $this->configsListFormatValidator->validate($rawConfigsList);


            /** @var array<array{name: string, schemes: string[]}> $rawConfigsList */

        } catch (UnableToReadFileException|UnableToDecodeJsonException|InvalidConfigsListFormatException $e) {
            throw new CriticalException($e instanceof UnableToReadFileException
                ? "Unable to read configs list"
                : "Invalid configs list format",
                $e->getMessage()
            );
        }


        /**
         * Read schemes
         */
        $schemes = $this->readSchemesListUseCase->handle();


        /**
         * Create empty configs map
         */
        $configs = new ConfigMap();

        foreach ($rawConfigsList as $rawConfig) {
            /**
             * Create empty config schemes map
             */
            $configSchemes = new UniqueSchemesMap();

            foreach ($rawConfig['schemes'] as $rawConfigScheme) {
                /**
                 * Try to find scheme with specific id
                 */
                try {
                    $scheme = $schemes->getById($rawConfigScheme);
                } catch (SchemeNotFoundException) {
                    continue;
                    //TODO: Add reporter event
                }


                /**
                 * Try to add found scheme to config schemes map
                 */
                try {
                    $configSchemes->add($scheme);
                } catch (SchemeAlreadyExistsException) {
                    continue;
                    //TODO: Add reporter event
                }
            }


            /**
             * Try to create new config and add it to configs map
             */
            try {
                $configs->add(
                    new Config(
                        new ConfigNameVO($rawConfig['name']),
                        $configSchemes)
                );
            } catch (ConfigAlreadyExistsException|InvalidConfigNameException) {
                continue;
                //TODO: Add reporter event
            }

        }

        return $configs;
    }
}