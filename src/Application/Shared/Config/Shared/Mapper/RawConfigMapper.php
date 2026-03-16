<?php

declare(strict_types=1);

namespace App\Application\Shared\Config\Shared\Mapper;

use App\Domain\Config\Entity\Config;
use App\Domain\Config\Exception\InvalidConfigNameException;
use App\Domain\Config\Exception\InvalidSchemeIdException;
use App\Domain\Shared\VO\Shared\SchemesIdsVO;

final readonly class RawConfigMapper
{
    /**
     * Map raw config as array to config entity
     *
     * @param array $rawConfig Raw config as array
     *
     * @throws InvalidConfigNameException
     * @throws InvalidSchemeIdException
     */
    public function map(array $rawConfig): Config
    {
        return new Config(
            $rawConfig['name'] ?? null,
            new SchemesIdsVO($rawConfig['schemes'] ?? []),
        );
    }
}