<?php

declare(strict_types=1);

namespace App\Application\Outbound\UseCase\OverrideOutbounds;

use App\Application\Outbound\DTO\UseCase\OverrideOutbounds\OverrideOutboundDTO;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Entity\ShadowsocksOutbound;
use App\Domain\Outbound\Entity\VlessOutbound;
use App\Domain\Outbound\VO\Shadowsocks\Userinfo\ShadowsocksUserinfoVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final readonly class OverrideOutboundsUseCase
{
    public function override(OverrideOutboundDTO $dto): OutboundMap
    {
        $outbounds = new OutboundMap();

        foreach ($dto->outboundMap->getOutbounds() as $outbound) {

            $outbounds->add(
                match (true) {
                    $outbound instanceof VlessOutbound && $dto->vlessUUID !== null => $this->overrideVlessOutbound($outbound, $dto->vlessUUID),
                    $outbound instanceof ShadowsocksOutbound && $dto->ssPass !== null => $this->overrideSSOutbound($outbound, $dto->ssPass),
                    default => $outbound
                }
            );
        }
        
        return $outbounds;
    }


    private function overrideVlessOutbound(VlessOutbound $outbound, NonEmptyStringVO $uuid): VlessOutbound
    {
        return new VlessOutbound(
            $outbound->getTagString(),
            $outbound->getId(),
            $outbound->getServer(),
            $outbound->getServerPort(),
            $uuid,
            $outbound->getFlow(),
            $outbound->getSecurity(),
            $outbound->getTransport()
        );
    }


    private function overrideSSOutbound(ShadowsocksOutbound $outbound, NonEmptyStringVO $pass): ShadowsocksOutbound
    {
        return new ShadowsocksOutbound(
            $outbound->getTagString(),
            $outbound->getId(),
            $outbound->getServer(),
            $outbound->getServerPort(),
            new ShadowsocksUserinfoVO(
                $outbound->getUserinfo()->getMethod(),
                $pass
            ),
            $outbound->getPlugin()
        );
    }
}