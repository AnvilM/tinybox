<?php

declare(strict_types=1);

namespace App\Application\UpdateSubscriptions\Mapper;

use App\Core\Domain\Scheme\Collection\SchemeCollection;
use App\Core\Domain\SingBox\Collection\OutboundCollection;
use App\Core\Domain\SingBox\Factory\OutboundFactory;
use App\Core\Shared\Ports\IO\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\UpdateSubscriptionsLifecycle\Mapper\OutboundsMapper\InvalidOutboundParamsReporterEvent;
use InvalidArgumentException;

final readonly class OutboundsMapper
{
    public function __construct(
        private ReporterPort $reporterPort,
    )
    {
    }

    /**
     * Maps Scheme entity collection to Outbound entity collection
     *
     * @param SchemeCollection $schemes Scheme entity collection
     * @param string $subscriptionName Subscription name for reporter event
     *
     * @return OutboundCollection Outbound entity collection
     *
     * @throws InvalidArgumentException If no one outbound is valid
     */
    public function map(SchemeCollection $schemes, string $subscriptionName): OutboundCollection
    {
        $outbounds = [];

        foreach ($schemes as $scheme) {
            try {
                $outbounds[] = OutboundFactory::fromScheme($scheme);
            } catch (InvalidArgumentException $exception) {
                $this->reporterPort->notify(new InvalidOutboundParamsReporterEvent(
                    $subscriptionName, $exception->getMessage(), $scheme->getTag()
                ));
            }
        }

        return OutboundCollection::create($outbounds);
    }
}