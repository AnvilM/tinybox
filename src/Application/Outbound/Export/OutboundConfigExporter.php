<?php

declare(strict_types=1);

namespace App\Application\Outbound\Export;

use App\Application\Outbound\Exception\Export\IncompatibleOutboundException;
use App\Application\Outbound\Exception\Export\UnsupportedByCoreException;
use App\Application\Outbound\Export\Interface\OutboundCompatibilitySpecificationInterface;
use App\Domain\Outbound\Entity\Outbound;

/**
 * @see OutboundConfigExporterInterface
 */
final readonly class OutboundConfigExporter implements OutboundConfigExporterInterface
{
    /**
     * @param ExporterRegistry $registry Registry of node exporters
     * @param iterable<OutboundCompatibilitySpecificationInterface> $specifications
     *        Cross-field compatibility rules checked before building the config
     */
    public function __construct(
        private ExporterRegistry $registry,
        private iterable         $specifications = [],
    )
    {
    }

    /**
     * @throws IncompatibleOutboundException|UnsupportedByCoreException
     */
    public function export(Outbound $outbound, CoreType $core): array
    {
        $this->assertCompatible($outbound, $core);

        return $this->cleanArray(
            $this->registry->export($outbound, $core)
        );
    }

    /**
     * @throws IncompatibleOutboundException
     */
    private function assertCompatible(Outbound $outbound, CoreType $core): void
    {
        foreach ($this->specifications as $specification) {
            if (!$specification->isSatisfiedBy($outbound, $core)) {
                throw new IncompatibleOutboundException($outbound, $core, $specification->reason());
            }
        }
    }

    private function cleanArray(array $array): array
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = $this->cleanArray($value);

                if (empty($value)) {
                    unset($array[$key]);
                }
            } elseif ($value === null || $value === '') {
                unset($array[$key]);
            }
        }

        return $array;
    }
}
