<?php

declare(strict_types=1);

namespace App\Application\Outbound\Exception\Export;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Shared\Exception\CoreException;

/**
 * Thrown when an outbound violates a registered
 * {@see \App\Application\Outbound\Export\Interface\OutboundCompatibilitySpecificationInterface}
 * for the given core.
 */
final class IncompatibleOutboundException extends CoreException
{
    public function __construct(
        private readonly Outbound $outbound,
        private readonly CoreType $core,
        private readonly string   $reason,
    )
    {
        parent::__construct(sprintf(
            'Outbound "%s" is not compatible with "%s" core: %s',
            $this->outbound->getTagString(),
            $this->core->value,
            $this->reason,
        ));
    }

    public function getOutbound(): Outbound
    {
        return $this->outbound;
    }

    public function getCore(): CoreType
    {
        return $this->core;
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
