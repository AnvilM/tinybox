<?php

declare(strict_types=1);

namespace App\Infrastructure\Config\Factory;

use App\Core\Shared\Ports\OS\Directories\GetConfigsDirectoryPort;
use App\Core\Shared\Ports\OS\Directories\GetDataHomeDirectoryPort;
use App\Core\Shared\VO\Config\ConfigVO;
use App\Core\Shared\VO\Config\SingBox\SingBoxConfigVO;
use App\Core\Shared\VO\Config\SingBox\Templates\TemplatesSingBoxConfigVO;

final readonly class DefaultConfigFactory
{
    public function __construct(
        private GetConfigsDirectoryPort  $getConfigsDirectory,
        private GetDataHomeDirectoryPort $getDataHomeDirectory,
    )
    {

    }

    public function create(): ConfigVO
    {
        return new ConfigVO(
            $this->getDataHomeDirectory->execute() . '/subscriptions.json',
            $this->getDataHomeDirectory->execute() . '/configs/',
            new SingBoxConfigVO(
                'sing-box',
                new TemplatesSingBoxConfigVO(
                    $this->getConfigsDirectory->execute() . '/templates/outbound.json',
                    $this->getConfigsDirectory->execute() . '/templates/urltest.json',
                    $this->getConfigsDirectory->execute() . '/templates/config.json',
                ),
                "/etc/sing-box/config.json",
                "sing-box"
            )

        );
    }

}