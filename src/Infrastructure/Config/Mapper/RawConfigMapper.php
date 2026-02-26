<?php

declare(strict_types=1);

namespace App\Infrastructure\Config\Mapper;

use App\Core\Shared\VO\Config\ConfigVO;
use App\Core\Shared\VO\Config\SingBox\SingBoxConfigVO;
use App\Core\Shared\VO\Config\SingBox\Templates\TemplatesSingBoxConfigVO;

final readonly class RawConfigMapper
{
    /**
     * Maps raw json decoded config array to config value object
     *
     * @param array $rawConfig Raw, json decoded config
     * @param ConfigVO $defaultConfig Default config used if some field in raw config not found
     *
     * @return ConfigVO Config value object
     */
    public function map(array $rawConfig, ConfigVO $defaultConfig): ConfigVO
    {
        return new ConfigVO(
            $rawConfig['subscriptions_list'] ?? $defaultConfig->subscriptionListPath,
            $rawConfig['generated_configs_dir'] ?? $defaultConfig->generatedConfigsDirectoryPath,
            new SingBoxConfigVO(
                $rawConfig['sing_box']['binary'] ?? $defaultConfig->singBoxConfig->binary,
                new TemplatesSingBoxConfigVO(
                    $rawConfig['sing_box']['templates']['outbound'] ?? $defaultConfig->singBoxConfig->templates->outbound,
                    $rawConfig['sing_box']['templates']['outbound_urltest'] ?? $defaultConfig->singBoxConfig->templates->outboundUrltest,
                    $rawConfig['sing_box']['templates']['config'] ?? $defaultConfig->singBoxConfig->templates->config,
                )
            )
        );
    }
}