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
use App\Domain\Config\Exception\InvalidSchemeIdException;
use App\Domain\Config\VO\NameVO;
use App\Domain\Config\VO\SchemeIdVO;
use App\Domain\Config\VO\SchemesIdsVO;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
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
            $schemeIdVo = new SchemeIdVO($command->schemeId);
        } catch (InvalidSchemeIdException) {
            throw new CriticalException("Invalid scheme id provided", $command->schemeId);
        }

        if (!$schemes->containsSchemeId($schemeIdVo->getSchemeId()))
            throw new CriticalException("Scheme with id {$schemeIdVo->getSchemeId()} does not exist");

        $configs = $this->readConfigsListUseCase->handle();


        try {
            $configName = new NameVO($command->name);
        } catch (InvalidConfigNameException) {
            throw new CriticalException("Invalid config name provided", $command->name);
        }


        try {
            $configs->getByName($configName)->getSchemesIds()->add($schemeIdVo);
        } catch (ConfigNotFoundException) {
            try {
                $configs->add(new Config(
                    $configName,
                    new SchemesIdsVO()->add($schemeIdVo),
                ));
            } catch (SchemeAlreadyExistsException) {
                throw new CriticalException("Scheme with id {$schemeIdVo->getSchemeId()} already exists in config {$configName->getName()} ", $command->schemeId);
            } catch (ConfigAlreadyExistsException) {
                throw new CriticalException("Unknown error");
            }
        } catch (SchemeAlreadyExistsException) {
            throw new CriticalException("Scheme with id {$schemeIdVo->getSchemeId()} already exists in config {$configName->getName()} ", $command->schemeId);

        }

        $this->writeConfigs->write(
            $configs
        );

    }
}