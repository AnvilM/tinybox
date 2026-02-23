<?php

declare(strict_types=1);

use Application\Kernel;
use React\EventLoop\Loop;

require 'vendor/autoload.php';

Kernel::createApp()->run(); 
