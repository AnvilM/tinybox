<?php

declare(strict_types=1);

namespace App\Application\Outbound\Filter\Specification;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Application\Outbound\Exception\Export\IncompatibleOutboundException;
use App\Application\Outbound\Exception\Export\UnsupportedByCoreException;
use App\Application\Outbound\Export\ExporterRegistryFactory;
use App\Domain\Interface\Outbound\OutboundSpecificationInterface;
use App\Domain\Outbound\Entity\Outbound;

final readonly class OutboundCoreSupportSpecification implements OutboundSpecificationInterface
{
    public function __construct(
        private CoreType $coreType,
    )
    {
    }

    public function isSatisfiedBy(Outbound $outbound): bool
    {
        try {
            ExporterRegistryFactory::createDefaultExporter()->export($outbound, $this->coreType);
        } catch (IncompatibleOutboundException|UnsupportedByCoreException) {
            return false;
        }
        return true;
    }
}