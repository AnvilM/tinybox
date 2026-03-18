<?php

declare(strict_types=1);

namespace Tests;

use Application\Bootstrappers\CommandsBootstrapper;
use Application\Bootstrappers\ProvidersBootstrapper;
use DI\Container;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;

abstract class BaseTestCase extends TestCase
{
    protected function getApp(array $services = []): Application
    {
        $app = new Application();

        CommandsBootstrapper::registerCommands($app,
            new Container(
                array_merge(ProvidersBootstrapper::getProviders(), $services)
            )
        );

        return $app;
    }


    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

}