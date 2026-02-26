<?php

declare(strict_types=1);

namespace Application\Bootstrappers;


use Application\Providers\App\Shared\Config\ConfigFactoryProvider;
use Application\Providers\App\Shared\SharedProviders;
use Application\Providers\ProviderInterface;

final class ProvidersBootstrapper
{

    /** @var array<class-string<ProviderInterface>> */
    private static array $providers = [
        SharedProviders::class
    ];

    /** @var array<class-string<ProviderInterface>> */
    private static array $appProviders = [

    ];

    /** @return array<string, mixed> */
    public static function getProviders(): array
    {
        return array_merge(
            ...array_map(
                static fn(string $provider): array => $provider::register(),
                array_merge(self::$appProviders, self::$providers)
            )
        );
    }
}