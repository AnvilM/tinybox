<?php

declare(strict_types=1);

namespace App\Core\Shared\Ports\Config\SingBox;

use App\Core\Shared\Exception\CriticalException;

interface SingBoxConfigPort
{
    /**
     * Sing-box outbound template path from config
     *
     * @return string Outbound template path
     *
     * @throws CriticalException Cannot find sing-box outbound template path
     */
    public static function singBoxOutboundTemplatePath(): string;

    /**
     * Sing-box urltest outbound template path from config
     *
     * @return string Urltest outbound template path
     *
     * @throws CriticalException Cannot find sing-box urltest outbound template path
     */
    public static function singBoxUrltestOutboundTemplatePath(): string;

    /**
     * Sing-box config template path from config
     *
     * @return string Sing-box config template path
     *
     * @throws CriticalException Cannot find sing-box config template path
     */
    public static function singBoxConfigTemplatePath(): string;

    /**
     * Sing-box config save directory path from config
     *
     * @return string Sing-box config save directory path
     *
     * @throws CriticalException Cannot find sing-box config save directory path
     */
    public static function singBoxConfigSaveDirectoryPath(): string;
}