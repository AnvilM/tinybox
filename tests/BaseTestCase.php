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
    private Container $container;

    protected function getApp(array $services = []): Application
    {
        $app = new Application();

        CommandsBootstrapper::registerCommands($app,
            $this->getContainer($services)
        );

        return $app;
    }

    protected function getContainer(array $services = []): Container
    {
        if (isset($this->container)) return $this->container;

        $this->container = new Container(array_merge(ProvidersBootstrapper::getProviders(), $services));

        return $this->container;
    }


    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

}