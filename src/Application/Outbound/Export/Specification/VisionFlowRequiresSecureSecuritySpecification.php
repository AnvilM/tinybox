<?php

declare(strict_types=1);

namespace App\Application\Outbound\Export\Specification;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Application\Outbound\Export\Interface\OutboundCompatibilitySpecificationInterface;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Entity\VlessOutbound;

/**
 * Example of a rule that can't be expressed in a single node exporter:
 * flow "xtls-rprx-vision" depends on both the flow field and the nested
 * SecurityVO type — two independent nodes.
 *
 * NOTE: the "xtls-rprx-vision" value and cores here illustrate the
 * pattern, not a real protocol constraint. Replace/remove for your
 * project's actual requirements.
 */
final class VisionFlowRequiresSecureSecuritySpecification implements OutboundCompatibilitySpecificationInterface
{
    private const string VISION_FLOW = 'xtls-rprx-vision';

    public function isSatisfiedBy(Outbound $outbound, CoreType $core): bool
    {
        if (!$outbound instanceof VlessOutbound || $outbound->getFlow() !== self::VISION_FLOW) {
            return true;
        }

        return $outbound->getSecurity() !== null;
    }

    public function reason(): string
    {
        return sprintf('flow "%s" requires a security (TLS or Reality) to be set', self::VISION_FLOW);
    }
}
