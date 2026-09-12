<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Factory\FromRawOutboundFactory\Outbound;

use App\Domain\Outbound\Entity\ShadowsocksOutbound;
use App\Domain\Outbound\VO\RawOutboundVO;
use App\Domain\Outbound\VO\Shadowsocks\Plugin\ShadowsocksPlugin;
use App\Domain\Outbound\VO\Shadowsocks\Plugin\ShadowsocksPluginVO;
use App\Domain\Outbound\VO\Shadowsocks\Userinfo\ShadowsocksMethod;
use App\Domain\Outbound\VO\Shadowsocks\Userinfo\ShadowsocksUserinfoVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\PortVO;
use InvalidArgumentException;

final readonly class FromRawOutboundShadowsocksOutboundFactory
{

    /**
     * Creates a shadowsocks outbound from raw outbound dto.
     *
     * @param RawOutboundVO $rawOutbound Raw outbound dto
     * @param NonEmptyStringVO $id Outbound id
     *
     * @return ShadowsocksOutbound Created shadowsocks outbound entity
     *
     * @throws InvalidArgumentException If required fields are missing or invalid
     */
    public function create(RawOutboundVO $rawOutbound, NonEmptyStringVO $id): ShadowsocksOutbound
    {
        [$method, $password] = $this->parseUserinfo($rawOutbound->uuid);

        $plugin = $rawOutbound->shadowsocksPlugin !== null
            ? $this->parsePlugin($rawOutbound->shadowsocksPlugin)
            : null;

        return new ShadowsocksOutbound(
            $rawOutbound->tag,
            $id,
            new NonEmptyStringVO($rawOutbound->server),
            new PortVO($rawOutbound->server_port),
            new ShadowsocksUserinfoVO(
                $method,
                new NonEmptyStringVO($password),
            ),
            $plugin,
        );
    }

    /**
     * Parses Shadowsocks userinfo.
     *
     * Supports both:
     * - method:password
     * - base64(method:password)
     *
     * @return array{0: ShadowsocksMethod, 1: string}
     *
     * @throws InvalidArgumentException If userinfo format is invalid
     */
    private function parseUserinfo(?string $userinfo): array
    {
        if ($userinfo === null || $userinfo === '') {
            throw new InvalidArgumentException('Userinfo cannot be empty');
        }

        $decodedUserinfo = str_contains($userinfo, ':')
            ? $userinfo
            : $this->decodeUserinfo($userinfo);

        [$method, $password] = explode(':', $decodedUserinfo, 2);

        if ($method === '') {
            throw new InvalidArgumentException('Method cannot be empty');
        }

        if ($password === '') {
            throw new InvalidArgumentException('Password cannot be empty');
        }

        $methodEnum = ShadowsocksMethod::tryFrom($method);

        if ($methodEnum === null) {
            throw new InvalidArgumentException(
                "Unsupported method: '{$method}'"
            );
        }

        return [$methodEnum, $password];
    }

    /**
     * Decodes base64-encoded Shadowsocks userinfo.
     *
     * @throws InvalidArgumentException If the value is not valid base64
     */
    private function decodeUserinfo(string $userinfo): string
    {
        $decoded = base64_decode($userinfo, true);

        if ($decoded === false || !str_contains($decoded, ':')) {
            throw new InvalidArgumentException('Invalid userinfo provided');
        }

        return $decoded;
    }

    /**
     * Parses raw Shadowsocks plugin.
     *
     * Supports:
     * - plugin
     * - plugin;options
     *
     * @throws InvalidArgumentException If plugin is unsupported
     */
    private function parsePlugin(string $plugin): ShadowsocksPluginVO
    {
        $decodedPlugin = urldecode($plugin);

        [$pluginName, $pluginOptions] = array_pad(
            explode(';', $decodedPlugin, 2),
            2,
            null,
        );

        if ($pluginName === '') {
            throw new InvalidArgumentException('Plugin cannot be empty');
        }

        $pluginEnum = ShadowsocksPlugin::tryFrom($pluginName);

        if ($pluginEnum === null) {
            throw new InvalidArgumentException(
                "Unsupported plugin: '{$pluginName}'"
            );
        }

        return new ShadowsocksPluginVO(
            $pluginEnum,
            $pluginOptions === null || $pluginOptions === ''
                ? null
                : new NonEmptyStringVO($pluginOptions),
        );
    }
}