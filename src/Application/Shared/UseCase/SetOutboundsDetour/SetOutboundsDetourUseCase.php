<?php

declare(strict_types=1);

namespace App\Application\Shared\UseCase\SetOutboundsDetour;

use App\Application\Shared\DTO\UseCase\SetOutboundsDetour\SetOutboundsDetourDTO;
use App\Domain\Interface\Subscription\DetourProvider;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;

final readonly class SetOutboundsDetourUseCase
{
    public function handle(SetOutboundsDetourDTO $dto): OutboundMap
    {
        $detourOutboundsMap = new OutboundMap();

        foreach ($dto->outbounds->getOutbounds() as $outbound) {
            if (!($outbound instanceof DetourProvider)) continue;

            if ($outbound->getTagString() !== $dto->detourOutbound->getTagString()) $outbound->setDetour($dto->detourOutbound);

            try {
                $detourOutboundsMap->add($outbound);
            } catch (OutboundAlreadyExistsException) {
                // TODO: Add reporter event
            }
        }

        return $detourOutboundsMap;
    }
}