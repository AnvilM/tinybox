<?php

declare(strict_types=1);

namespace App\Application\Outbound\Export\Specification;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Application\Outbound\Export\Interface\OutboundCompatibilitySpecificationInterface;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Entity\VlessOutbound;

/**
 * sing-box accepts only "xtls-rprx-vision" as vless flow and fails at startup
 * on anything else (e.g. the legacy "xtls-rprx-vision-udp443" that some
 * subscriptions still use). Xray has no such restriction.
 */
final class SingBoxVlessFlowSpecification implements OutboundCompatibilitySpecificationInterface
{
    private const string VISION_FLOW = 'xtls-rprx-vision';

    public function isSatisfiedBy(Outbound $outbound, CoreType $core): bool
    {
        if ($core !== CoreType::SingBox || !$outbound instanceof VlessOutbound) return true;

        $flow = $outbound->getFlowString();

        return $flow === null || $flow === '' || $flow === self::VISION_FLOW;
    }

    public function reason(): string
    {
        return sprintf('sing-box supports only "%s" vless flow', self::VISION_FLOW);
    }
}
