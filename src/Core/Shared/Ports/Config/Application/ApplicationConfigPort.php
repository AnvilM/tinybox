<?php

declare(strict_types=1);

namespace App\Core\Shared\Ports\Config\Application;

interface ApplicationConfigPort
{
    /**
     * Is application runs in debug mode
     *
     * @return bool Is debug
     */
    public static function isDebug(): bool;
}