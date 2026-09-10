<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO\Shadowsocks\Plugin;

use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final readonly class ShadowsocksPluginVO
{

    private ShadowsocksPlugin $plugin;

    private ?NonEmptyStringVO $pluginOptions;

    public function __construct(ShadowsocksPlugin $plugin, ?NonEmptyStringVO $pluginOptions)
    {
        $this->plugin = $plugin;
        $this->pluginOptions = $pluginOptions;
    }


    /**
     * Get shadowsocks plugin
     *
     * @return ShadowsocksPlugin Shadowsocks plugin
     */
    public function getPlugin(): ShadowsocksPlugin
    {
        return $this->plugin;
    }


    /**
     * Get plugin options or null if empty
     *
     * @return string|null Plugin options
     */
    public function getPluginOptions(): ?string
    {
        return $this->pluginOptions->getValue();
    }
}