<?php

declare(strict_types=1);

namespace App\Application\Services\Config\AddSchemeToConfig\Handler;

use App\Application\Services\Config\AddSchemeToConfig\Command\AddSchemeToConfigCommand;
use App\Application\Shared\Config\Shared\File\WriteConfigs;
use App\Application\Shared\Config\UseCase\ReadConfigsList\ReadConfigsListUseCase;
use App\Application\Shared\Scheme\UseCase\ReadSchemesList\ReadSchemesListUseCase;
use App\Domain\Config\Entity\Config;
use App\Domain\Config\Exception\ConfigAlreadyExistsException;
use App\Domain\Config\Exception\ConfigNotFoundException;
use App\Domain\Config\Exception\InvalidConfigNameException;
use App\Domain\Config\VO\ConfigNameVO;
use App\Domain\Scheme\Collection\UniqueSchemesMap;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use App\Domain\Scheme\Exception\SchemeNotFoundException;
use App\Domain\Shared\Exception\CriticalException;

final readonly class AddSchemeToConfigHandler
{
    public function __construct(
        private ReadSchemesListUseCase $readSchemesListUseCase,
        private ReadConfigsListUseCase $readConfigsListUseCase,
        private WriteConfigs           $writeConfigs,
    )
    {
    }

    /**
     * Add scheme to config
     *
     * @param AddSchemeToConfigCommand $command Command with config name and scheme id
     *
     * @throws CriticalException
     */
    public function handle(AddSchemeToConfigCommand $command): void
    {
        /**
         * Read schemes list
         */
        $schemes = $this->readSchemesListUseCase->handle();


        /**
         * Try to find scheme with provided id in schemes list
         */
        try {
            $scheme = $schemes->getById($command->schemeId);
        } catch (SchemeNotFoundException) {
            throw new CriticalException("Scheme with id $command->schemeId does not exist");
        }


        /**
         * Read configs list
         */
        $configs = $this->readConfigsListUseCase->handle();


        /**
         * Try to create config name
         */
        try {
            $configName = new ConfigNameVO($command->name);
        } catch (InvalidConfigNameException) {
            throw new CriticalException("Invalid config name provided", $command->name);
        }


        /**
         * Try to find config with provided name in config list
         */
        try {
            $configs->getByName($configName)->getSchemes()->add($scheme);
        } catch (ConfigNotFoundException) {
            $schemes = new UniqueSchemesMap();

            /**
             * Try to create new config with scheme found by provided scheme id
             */
            try {
                $schemes->add($scheme);

                $configs->add(new Config(
                    $configName,
                    $schemes
                ));
            } catch (SchemeAlreadyExistsException) {
                throw new CriticalException("Scheme with id {$scheme->getHash()} already exists in config {$configName->getName()} ", $command->schemeId);
            } catch (ConfigAlreadyExistsException) {
                throw new CriticalException("Unknown error");
            }
        } catch (SchemeAlreadyExistsException) {
            throw new CriticalException("Scheme with id {$scheme->getHash()} already exists in config {$configName->getName()} ", $command->schemeId);

        }


        /**
         * Write configs list to file
         */
        $this->writeConfigs->write(
            $configs
        );

    }
}