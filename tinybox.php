#!/usr/bin/env php
<?php

declare(strict_types=1);

use Application\Kernel;

require __DIR__ . '/vendor/autoload.php';

Kernel::createApp()->run(); 
