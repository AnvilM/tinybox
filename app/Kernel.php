<?php

declare(strict_types=1);

namespace Application;

use Application\Bootstrappers\CommandsBootstrapper;
use Application\Bootstrappers\ContainerBootstrapper;
use Application\Bootstrappers\ProvidersBootstrapper;
use Application\Config\ApplicationConfig\ApplicationConfig;
use Iva\Application;


final readonly class Kernel
{

    /**
     * @return Application
     */
    public static function createApp(): Application
    {
        $app = new Application(ApplicationConfig::appName, ApplicationConfig::appVersion);

        CommandsBootstrapper::registerCommands($app,
            ContainerBootstrapper::createContainer(
                ProvidersBootstrapper::getProviders()
            ));

        return $app;
    }


}