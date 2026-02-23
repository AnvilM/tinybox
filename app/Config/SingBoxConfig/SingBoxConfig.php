<?php

declare(strict_types=1);

namespace Application\Config\SingBoxConfig;

use App\Core\Exceptions\ApplicationException;
use Application\Config\ApplicationConfig\ApplicationConfig;
use RuntimeException;

final readonly class SingBoxConfig
{
    /**
     * @throws RuntimeException
     * @throws ApplicationException
     */
    public static function singBoxConfigTemplatePath(): string
    {
        return ApplicationConfig::configDirectoryPath() . '/sing-box.template.json';
    }

    /**
     * @throws RuntimeException
     * @throws ApplicationException
     */
    public static function singBoxOutboundTemplatePath(): string
    {
        return ApplicationConfig::configDirectoryPath() . '/outbound.template.json';
    }

    /**
     * @throws RuntimeException
     * @throws ApplicationException
     */
    public static function singBoxUrltestOutboundTemplatePath(): string
    {
        return ApplicationConfig::configDirectoryPath() . '/outbound.urltest.template.json';
    }

    /**
     * @throws RuntimeException
     * @throws ApplicationException
     */
    public static function singBoxConfigSaveDirectoryPath(): string
    {
        return ApplicationConfig::dataHomeDirectoryPath() . '/configs/';
    }

    
    
    
}