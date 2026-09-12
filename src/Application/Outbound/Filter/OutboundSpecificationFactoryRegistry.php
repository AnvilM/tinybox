<?php

declare(strict_types=1);

namespace App\Application\Outbound\Filter;

use App\Application\Outbound\Exception\Filter\UnsupportedOutboundFilterCriteriaException;
use App\Application\Outbound\Filter\Contract\OutboundFilterCriteriaInterface;
use App\Application\Outbound\Filter\Contract\OutboundSpecificationFactoryInterface;
use App\Application\Outbound\Filter\SpecificationFactory\OutboundCoreSupportSpecificationFactory;
use App\Application\Outbound\Filter\SpecificationFactory\OutboundCountryCodeSpecificationFactory;
use App\Application\Outbound\Filter\SpecificationFactory\OutboundExcludeCountryCodeSpecificationFactory;
use App\Application\Outbound\Filter\SpecificationFactory\OutboundExcludeTagSpecificationFactory;
use App\Application\Outbound\Filter\SpecificationFactory\OutboundExcludeTypeSpecificationFactory;
use App\Application\Outbound\Filter\SpecificationFactory\OutboundTypeSpecificationFactory;
use App\Domain\Interface\Outbound\OutboundSpecificationInterface;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Exception\CriticalException;
use Psl\Collection\Vector;
use Psl\Collection\VectorInterface;

/**
 * Central place that knows every available filter rule.
 *
 * ## Why the constructor lists concrete factories instead of `iterable $factories`
 *
 * An `iterable`/array constructor parameter cannot be autowired by PHP-DI (or
 * most PSR-11 containers): the container has no way to guess which services
 * belong in it, so it would have to be spelled out by hand in a container
 * definitions file - i.e. application wiring would leak into infrastructure
 * config, which is exactly what we want to avoid.
 *
 * Every constructor parameter below is a concrete, unambiguous class, so
 * PHP-DI resolves this whole registry automatically. Nothing needs to be
 * registered in any container definitions file for outbound filtering to
 * work - this class *is* the wiring, and it's plain, autowirable PHP.
 *
 * To add a new filter rule:
 *  1. Add a new `*Criteria` class (Filter/Criteria).
 *  2. Add a new specification implementing the domain
 *     `OutboundSpecificationInterface` (Filter/Specification).
 *  3. Add a new `*SpecificationFactory` connecting the two
 *     (Filter/SpecificationFactory).
 *  4. Add it as a new typed constructor parameter here and push it into
 *     `$this->factories` below. This is the *only* file that needs
 *     touching to wire a new rule in - no container config, nothing else.
 */
final readonly class OutboundSpecificationFactoryRegistry
{
    /**
     * @var VectorInterface<OutboundSpecificationFactoryInterface>
     */
    private VectorInterface $factories;

    public function __construct(
        OutboundExcludeTagSpecificationFactory         $excludeTagSpecificationFactory,
        OutboundTypeSpecificationFactory               $typeSpecificationFactory,
        OutboundExcludeTypeSpecificationFactory        $excludeTypeSpecificationFactory,
        OutboundCountryCodeSpecificationFactory        $countryCodeSpecificationFactory,
        OutboundExcludeCountryCodeSpecificationFactory $excludeCountryCodeSpecificationFactory,
        OutboundCoreSupportSpecificationFactory        $coreSupportSpecificationFactory,
    )
    {
        $this->factories = new Vector([
            $excludeTagSpecificationFactory,
            $typeSpecificationFactory,
            $excludeTypeSpecificationFactory,
            $countryCodeSpecificationFactory,
            $excludeCountryCodeSpecificationFactory,
            $coreSupportSpecificationFactory,
        ]);
    }

    /**
     * @throws CriticalException
     */
    public function resolve(OutboundFilterCriteriaInterface $criteria, OutboundMap $outboundsMap): OutboundSpecificationInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($criteria)) {
                return $factory->create($criteria, $outboundsMap);
            }
        }

        throw new UnsupportedOutboundFilterCriteriaException($criteria);
    }
}