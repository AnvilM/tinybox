<?php

declare(strict_types=1);

namespace App\Application\AddSchemeToConfig\Handler;

use App\Application\AddSchemeToConfig\Command\AddSchemeToConfigCommand;
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
     * @throws CriticalException
     */
    public function handle(AddSchemeToConfigCommand $command): void
    {
        $schemes = $this->readSchemesListUseCase->handle();

        try {
            $scheme = $schemes->getById($command->schemeId);
        } catch (SchemeNotFoundException) {
            throw new CriticalException("Scheme with id $command->schemeId does not exist");
        }

        $configs = $this->readConfigsListUseCase->handle();


        try {
            $configName = new ConfigNameVO($command->name);
        } catch (InvalidConfigNameException) {
            throw new CriticalException("Invalid config name provided", $command->name);
        }


        try {
            $configs->getByName($configName)->getSchemes()->add($scheme);
        } catch (ConfigNotFoundException) {
            $schemes = new UniqueSchemesMap();


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

        $this->writeConfigs->write(
            $configs
        );

    }
}