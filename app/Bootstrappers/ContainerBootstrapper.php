<?php

declare(strict_types=1);

namespace Application\Bootstrappers;

use DI\Container;
use Psr\Container\ContainerInterface;

final readonly class ContainerBootstrapper
{
    /** @param array<string, mixed> $providers */
    public static function createContainer(array $providers): ContainerInterface
    {
        return new Container(
            $providers
        );
    }
}