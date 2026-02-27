<?php

declare(strict_types=1);

namespace App\Infrastructure\Config\Factory;

use App\Core\Shared\VO\Config\ConfigVO;
use App\Core\Shared\VO\Config\SingBox\SingBoxConfigVO;
use App\Core\Shared\VO\Config\SingBox\Templates\TemplatesSingBoxConfigVO;
use App\Infrastructure\Shared\OS\Helper\GetConfigsDirectory;
use App\Infrastructure\Shared\OS\Helper\GetDataHomeDirectory;

final readonly class DefaultConfigFactory
{
    public function __construct(
        private GetConfigsDirectory  $getConfigsDirectory,
        private GetDataHomeDirectory $getDataHomeDirectory,
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
                )
            )

        );
    }

}