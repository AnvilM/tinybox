#!/usr/bin/env php
<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

use Application\Kernel;

require __DIR__ . '/vendor/autoload.php';

Psl\Async\main(static function (): int {
    Kernel::createApp()->run();

    return 0;
});

