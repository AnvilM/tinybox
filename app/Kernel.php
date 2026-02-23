<?php

declare(strict_types=1);

namespace Application;

use Application\Bootstrappers\CommandsBootstrapper;
use Application\Bootstrappers\ContainerBootstrapper;
use Application\Bootstrappers\ProvidersBootstrapper;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

final readonly class Kernel
{

    /**
     * @return Application
     */
    public static function createApp(): Application
    {
        $app = new Application();

        CommandsBootstrapper::registerCommands($app,
            ContainerBootstrapper::createContainer(
                ProvidersBootstrapper::getProviders()
            ));

        return $app;
    }

    
}