<?php

declare(strict_types=1);

namespace App\Infrastructure\Config\Factory;

use App\Domain\Shared\Ports\OS\Directories\GetConfigsDirectoryPort;
use App\Domain\Shared\Ports\OS\Directories\GetDataHomeDirectoryPort;
use App\Domain\Shared\VO\Config\ConfigVO;
use App\Domain\Shared\VO\Config\SingBox\OutboundTest\FetchIp\FetchIpOutboundTestSingBoxConfigVO;
use App\Domain\Shared\VO\Config\SingBox\OutboundTest\Latency\LatencyTestMethod;
use App\Domain\Shared\VO\Config\SingBox\OutboundTest\Latency\OutboundLatencyTestSingBoxConfigVO;
use App\Domain\Shared\VO\Config\SingBox\OutboundTest\OutboundTestSingBoxConfigVO;
use App\Domain\Shared\VO\Config\SingBox\OutboundTest\Templates\OutboundTestTemplatesSingBoxConfigVO;
use App\Domain\Shared\VO\Config\SingBox\SingBoxConfigVO;
use App\Domain\Shared\VO\Config\SingBox\Templates\TemplatesSingBoxConfigVO;
use App\Domain\Shared\VO\Config\Subscriptions\SubscriptionsConfigVO;

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
            $this->getDataHomeDirectory->execute() . '/scheme_groups.json',
            $this->getDataHomeDirectory->execute() . '/schemes.json',
            new SubscriptionsConfigVO(
                10,
                "tinybox/0.1",
                null
            ),
            new SingBoxConfigVO(
                'sing-box',
                new TemplatesSingBoxConfigVO(
                    $this->getConfigsDirectory->execute() . '/templates/outbound.json',
                    $this->getConfigsDirectory->execute() . '/templates/urltest.json',
                    $this->getConfigsDirectory->execute() . '/templates/config.json',
                ),
                "/etc/sing-box/config.json",
                "sing-box",
                new OutboundTestSingBoxConfigVO(
                    new OutboundTestTemplatesSingBoxConfigVO(
                        $this->getConfigsDirectory->execute() . '/templates/outbound.json',
                        $this->getConfigsDirectory->execute() . '/templates/config.json',
                    ),
                    $this->getDataHomeDirectory->execute() . '/outbound_test/sing-box_config.json',
                    new FetchIpOutboundTestSingBoxConfigVO(
                        $this->getDataHomeDirectory->execute() . '/geoip.mmdb',
                        "https://ifconfig.me/ip"
                    ),
                    3,
                    new OutboundLatencyTestSingBoxConfigVO(
                        "https://google.com",
                        LatencyTestMethod::PROXY_GET,
                    ),
                    10
                )
            ),
        );
    }

}