<?php

declare(strict_types=1);

namespace App\Infrastructure\Config\Factory;

use App\Domain\Shared\Ports\OS\Path\NormalizePathPort;
use App\Domain\Shared\VO\Config\ConfigVO;
use App\Domain\Shared\VO\Config\SingBox\OutboundTest\FetchIp\FetchIpOutboundTestSingBoxConfigVO;
use App\Domain\Shared\VO\Config\SingBox\OutboundTest\Latency\LatencyTestMethod;
use App\Domain\Shared\VO\Config\SingBox\OutboundTest\Latency\OutboundLatencyTestSingBoxConfigVO;
use App\Domain\Shared\VO\Config\SingBox\OutboundTest\OutboundTestSingBoxConfigVO;
use App\Domain\Shared\VO\Config\SingBox\OutboundTest\Templates\OutboundTestTemplatesSingBoxConfigVO;
use App\Domain\Shared\VO\Config\SingBox\SingBoxConfigVO;
use App\Domain\Shared\VO\Config\SingBox\Templates\TemplatesSingBoxConfigVO;

final readonly class ConfigFactory
{
    public function __construct(
        private NormalizePathPort $normalizePathPort,
    )
    {
    }

    /**
     * Creates config value object from raw json decoded config array
     *
     * @param array $rawConfig Raw, json decoded config
     * @param ConfigVO $defaultConfig Default config used if some field in raw config not found
     *
     * @return ConfigVO SchemeGroup value object
     */
    public function create(array $rawConfig, ConfigVO $defaultConfig): ConfigVO
    {
        return new ConfigVO(
            $this->normalizePath($rawConfig['subscriptions_list'] ?? $defaultConfig->subscriptionsListPath),
            $this->normalizePath($rawConfig['scheme_groups_list'] ?? $defaultConfig->schemeGroupsListPath),
            $this->normalizePath($rawConfig['schemes_list'] ?? $defaultConfig->schemesListPath),
            new SingBoxConfigVO(
                $rawConfig['sing_box']['binary'] ?? $defaultConfig->singBoxConfig->binary,
                new TemplatesSingBoxConfigVO(
                    $this->normalizePath($rawConfig['sing_box']['templates']['outbound'] ?? $defaultConfig->singBoxConfig->templates->outbound),
                    $this->normalizePath($rawConfig['sing_box']['templates']['outbound_urltest'] ?? $defaultConfig->singBoxConfig->templates->outboundUrltest),
                    $this->normalizePath($rawConfig['sing_box']['templates']['config'] ?? $defaultConfig->singBoxConfig->templates->config),
                ),
                $this->normalizePath($rawConfig['sing_box']['default_config_path'] ?? $defaultConfig->singBoxConfig->defaultConfigPath),
                $rawConfig['sing_box']['systemd_service_name'] ?? $defaultConfig->singBoxConfig->systemdServiceName,
                new OutboundTestSingBoxConfigVO(
                    new OutboundTestTemplatesSingBoxConfigVO(
                        $this->normalizePath($rawConfig['sing_box']['outbound_test']['templates']['outbound'] ?? $defaultConfig->singBoxConfig->outboundTest->templates->outbound),
                        $this->normalizePath($rawConfig['sing_box']['outbound_test']['templates']['config'] ?? $defaultConfig->singBoxConfig->outboundTest->templates->config),
                    ),
                    $this->normalizePath($rawConfig['sing_box']['outbound_test']['sing_box_config'] ?? $defaultConfig->singBoxConfig->outboundTest->singBoxConfig),
                    new FetchIpOutboundTestSingBoxConfigVO(
                        $this->normalizePath($rawConfig['sing_box']['outbound_test']['fetch_ip']['geoip_database'] ?? $defaultConfig->singBoxConfig->outboundTest->fetchIp->geoIpDatabase),
                        $rawConfig['sing_box']['outbound_test']['fetch_ip']['url'] ?? $defaultConfig->singBoxConfig->outboundTest->fetchIp->url,
                    ),
                    $rawConfig['sing_box']['outbound_test']['max_parallel_requests'] ?? $defaultConfig->singBoxConfig->outboundTest->maxParallelRequests,
                    new OutboundLatencyTestSingBoxConfigVO(
                        $rawConfig['sing_box']['outbound_test']['latency']['url'] ?? $defaultConfig->singBoxConfig->outboundTest->latency->url,
                        LatencyTestMethod::tryFrom($rawConfig['sing_box']['outbound_test']['latency']['method'] ?? '') ?? $defaultConfig->singBoxConfig->outboundTest->latency->method,
                        $rawConfig['sing_box']['outbound_test']['latency']['tcp_ping_timeout'] ?? $defaultConfig->singBoxConfig->outboundTest->latency->tcpPingTimeout
                    )
                ),
            ),
        );
    }

    private function normalizePath(?string $path): ?string
    {
        return $path === null
            ? null
            : $this->normalizePathPort->execute($path);
    }
}