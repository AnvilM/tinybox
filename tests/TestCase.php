<?php

namespace Tests;

use Application\Kernel;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Slim\App;
use Symfony\Component\Console\Application as CliApp;

abstract class TestCase extends BaseTestCase
{
    protected App $app;
    protected CliApp $cliApp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = Kernel::createApp();
        $this->cliApp = Kernel::createCliApp();
    }


}
