<?php

declare(strict_types=1);

namespace App\Application\Outbound\Export\Interface;

use App\Application\Outbound\Export\CoreType;
use App\Domain\Outbound\Entity\Outbound;

/**
 * A compatibility rule spanning multiple Outbound fields at once.
 *
 * {@see NodeExporterInterface::supports()} only covers local constraints
 * of a single node ("Reality unsupported by sing-box"). Some rules depend
 * on a combination of independent nodes — e.g. "flow=xtls-rprx-vision
 * requires TLS/Reality security and is incompatible with WebSocket
 * transport". Such rules get their own implementations of this interface,
 * checked before building the config, instead of scattering conditional
 * logic across exporters.
 */
interface OutboundCompatibilitySpecificationInterface
{
    /**
     * Checks whether the outbound satisfies the rule for the given core.
     *
     * @param Outbound $outbound Outbound being checked
     * @param CoreType $core Target proxy core
     *
     * @return bool True if satisfied (or not applicable to this outbound)
     */
    public function isSatisfiedBy(Outbound $outbound, CoreType $core): bool;

    /**
     * Human-readable explanation of why the rule may be violated.
     *
     * Used in the exception message when the rule isn't satisfied.
     *
     * @return string Reason/description of the rule
     */
    public function reason(): string;
}
