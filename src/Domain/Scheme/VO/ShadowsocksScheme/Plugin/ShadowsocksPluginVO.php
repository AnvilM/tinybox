<?php

declare(strict_types=1);

namespace App\Domain\Scheme\VO\ShadowsocksScheme\Plugin;

use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use InvalidArgumentException;

final readonly class ShadowsocksPluginVO extends NonEmptyStringVO
{

    private ShadowsocksPlugin $plugin;

    private ?string $pluginOptions;

    /**
     * @param string|null $plugin Raw plugin string
     *
     * @throws InvalidArgumentException If plugin is unsupported
     */
    public function __construct(?string $plugin)
    {
        parent::__construct($plugin);

        $decodedPlugin = urldecode($plugin);

        $explodedPlugin = explode(';', $decodedPlugin, 2);

        $this->plugin = ShadowsocksPlugin::tryFrom($explodedPlugin[0]) ?? throw new InvalidArgumentException("Unsupported plugin: " . "'" . ($explodedPlugin[0] ?? 'null') . "'");

        $this->pluginOptions = $explodedPlugin[1] ?? null;
    }


    /**
     * Get raw plugin string
     *
     * @return string Raw plugin string
     */
    public function getRawPlugin(): string
    {
        return $this->getValue();
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
        return $this->pluginOptions;
    }
}