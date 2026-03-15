<?php

declare(strict_types=1);

namespace App\Infrastructure\Config\Factory;

use App\Domain\Shared\Ports\OS\Path\NormalizePathPort;
use App\Domain\Shared\VO\Config\ConfigVO;
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
     * @return ConfigVO Config value object
     */
    public function create(array $rawConfig, ConfigVO $defaultConfig): ConfigVO
    {
        return new ConfigVO(
            $this->normalizePath($rawConfig['subscriptions_list'] ?? null) ?? $defaultConfig->subscriptionsListPath,
            $this->normalizePath($rawConfig['configs_list'] ?? null) ?? $defaultConfig->configsListPath,
            $this->normalizePath($rawConfig['schemes_list'] ?? null) ?? $defaultConfig->schemesListPath,
            new SingBoxConfigVO(
                $rawConfig['sing_box']['binary'] ?? $defaultConfig->singBoxConfig->binary,
                new TemplatesSingBoxConfigVO(
                    $this->normalizePath($rawConfig['sing_box']['templates']['outbound'] ?? null) ?? $defaultConfig->singBoxConfig->templates->outbound,
                    $this->normalizePath($rawConfig['sing_box']['templates']['outbound_urltest'] ?? null) ?? $defaultConfig->singBoxConfig->templates->outboundUrltest,
                    $this->normalizePath($rawConfig['sing_box']['templates']['config'] ?? null) ?? $defaultConfig->singBoxConfig->templates->config,
                ),
                $this->normalizePath($rawConfig['sing_box']['default_config_path'] ?? null) ?? $defaultConfig->singBoxConfig->defaultConfigPath,
                $rawConfig['sing_box']['systemd_service_name'] ?? $defaultConfig->singBoxConfig->systemdServiceName

            )
        );
    }

    private function normalizePath(?string $path): ?string
    {
        return $path === null
            ? null
            : $this->normalizePathPort->execute($path);
    }
}