<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Factory\FromRawOutbound;

use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Entity\ShadowsocksOutbound;
use App\Domain\Outbound\Entity\TLS\Reality;
use App\Domain\Outbound\Entity\TLS\TLS;
use App\Domain\Outbound\Entity\TLS\UTLS;
use App\Domain\Outbound\Entity\VlessOutbound;
use App\Domain\Outbound\Exception\UnsupportedOutboundTypeException;
use App\Domain\Outbound\VO\RawOutbound\RawOutboundVO;
use App\Domain\Outbound\VO\RawOutbound\RawShadowsocksOutboundVO;
use App\Domain\Outbound\VO\RawOutbound\RawVlessOutboundVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\PortVO;
use InvalidArgumentException;

final readonly class FromRawOutboundFactory
{
    /**
     * Create outbound entity from raw outbound value object
     *
     * @param RawOutboundVO $rawOutboundVO Raw outbound value object
     * @param int $id Outbound id
     *
     * @return Outbound Outbound entity
     *
     * @throws InvalidArgumentException
     * @throws UnsupportedOutboundTypeException
     */
    public function fromRawOutboundVO(RawOutboundVO $rawOutboundVO, int $id): Outbound
    {
        if ($rawOutboundVO instanceof RawVlessOutboundVO) {
            return $this->fromVlessRawOutbound($rawOutboundVO, $id);
        }

        if ($rawOutboundVO instanceof RawShadowsocksOutboundVO) {
            return $this->fromShadowsocksRawOutbound($rawOutboundVO, $id);
        }

        throw new UnsupportedOutboundTypeException($rawOutboundVO->type);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function fromVlessRawOutbound(RawVlessOutboundVO $rawVlessOutboundVO, int $id): VlessOutbound
    {
        return new VlessOutbound(
            new NonEmptyStringVO($rawVlessOutboundVO->tag),
            $id,
            new NonEmptyStringVO($rawVlessOutboundVO->server),
            new PortVO($rawVlessOutboundVO->serverPort),
            new NonEmptyStringVO($rawVlessOutboundVO->uuid),
            $rawVlessOutboundVO->flow == null ? null : new NonEmptyStringVO($rawVlessOutboundVO->flow),
            new TLS(
                new NonEmptyStringVO($rawVlessOutboundVO->tls?->serverName),
                new Reality(
                    new NonEmptyStringVO($rawVlessOutboundVO->tls?->reality?->publicKey),
                    new NonEmptyStringVO($rawVlessOutboundVO->tls?->reality?->shortId),
                    $rawVlessOutboundVO->tls?->reality?->enabled,
                ),
                new UTLS(
                    new NonEmptyStringVO($rawVlessOutboundVO->tls?->utls?->fingerprint),
                    $rawVlessOutboundVO->tls?->utls?->enabled,
                ),
                $rawVlessOutboundVO->tls?->enabled,
            )
        );
    }


    /**
     * @throws InvalidArgumentException
     */
    private function fromShadowsocksRawOutbound(RawShadowsocksOutboundVO $rawShadowsocksOutboundVO, int $id): ShadowsocksOutbound
    {
        return new ShadowsocksOutbound(
            new NonEmptyStringVO($rawShadowsocksOutboundVO->tag),
            $id,
            new NonEmptyStringVO($rawShadowsocksOutboundVO->server),
            new PortVO($rawShadowsocksOutboundVO->serverPort),
            new NonEmptyStringVO($rawShadowsocksOutboundVO->method),
            new NonEmptyStringVO($rawShadowsocksOutboundVO->password),
            $rawShadowsocksOutboundVO->plugin == null ? null : new NonEmptyStringVO($rawShadowsocksOutboundVO->plugin),
            $rawShadowsocksOutboundVO->pluginOptions == null ? null : new NonEmptyStringVO($rawShadowsocksOutboundVO->pluginOptions),
        );
    }
}