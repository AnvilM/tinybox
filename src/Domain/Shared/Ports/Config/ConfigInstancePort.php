<?php

declare(strict_types=1);

namespace App\Domain\Shared\Ports\Config;

use App\Domain\Shared\VO\Config\ConfigVO;

interface ConfigInstancePort
{
    /**
     * Get config value object
     *
     * @return ConfigVO SchemeGroup value object
     */
    public function get(): ConfigVO;
}