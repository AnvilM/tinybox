<?php

declare(strict_types=1);

namespace App\Core\Shared\Ports\Config;

use App\Core\Shared\VO\Config\ConfigVO;

interface ConfigInstancePort
{
    /**
     * Get config value object
     *
     * @return ConfigVO Config value object
     */
    public function get(): ConfigVO;
}