<?php

declare(strict_types=1);

namespace App\Application\Outbound\Exception\Filter;

use App\Application\Outbound\Filter\Interface\OutboundFilterCriteriaInterface;
use App\Domain\Shared\Exception\CriticalException;

/**
 * Thrown when no registered {@see \App\Application\Outbound\Filter\Interface\OutboundSpecificationFactoryInterface}
 * knows how to handle a given criteria.
 *
 * NOTE: In practice this signals a wiring mistake (a new Criteria class was
 * added without a matching factory being registered), not a user error.
 */
final class UnsupportedOutboundFilterCriteriaException extends CriticalException
{
    public function __construct(OutboundFilterCriteriaInterface $criteria)
    {
        parent::__construct(
            'Unsupported outbound filter criteria',
            sprintf('No specification factory is registered for criteria "%s"', $criteria::class),
        );
    }
}
