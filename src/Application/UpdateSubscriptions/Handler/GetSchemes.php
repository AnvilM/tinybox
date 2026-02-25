<?php

declare(strict_types=1);

namespace App\Application\UpdateSubscriptions\Handler;

use App\Application\UpdateSubscriptions\Fetch\FetchSubscriptions;
use App\Application\UpdateSubscriptions\Mapper\RawSchemesMapper;
use App\Application\UpdateSubscriptions\Parser\RawSchemesParser;
use App\Core\Domain\Scheme\Collection\SchemeMap;
use App\Core\Domain\Subscription\Collection\SubscriptionCollection;
use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Ports\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\UpdateSubscriptionsLifecycle\Handler\GetSchemes\NoValidSchemesInSubscriptionFoundReporterEvent;
use InvalidArgumentException;

final readonly class GetSchemes
{
    public function __construct(
        private FetchSubscriptions $loadSchemes,
        private RawSchemesParser   $rawSchemeParser,
        private RawSchemesMapper   $rawSchemesMapper,
        private ReporterPort       $reporterPort,
    )
    {
    }

    public function get(SubscriptionCollection $subscriptions): SchemeMap
    {

        /**
         * Fetching schemes string from subscription url
         */
        $rawSchemesStringArray = $this->loadSchemes->load(
            $subscriptions
        );

        $schemeMap = new SchemeMap();

        /**
         * Parse schemes strings and map to SchemeMap e.g., ["subscriptionName" => SchemeCollection]
         */
        foreach ($rawSchemesStringArray as $subscriptionName => $rawSchemesString) {


            try {
                $schemeMap[$subscriptionName] = $this->rawSchemesMapper->map(
                    $this->rawSchemeParser->parse(
                        $rawSchemesString,
                        $subscriptionName
                    ), $subscriptionName
                );
            } catch (InvalidArgumentException) {
                $this->reporterPort->notify(new NoValidSchemesInSubscriptionFoundReporterEvent(
                    $subscriptionName,
                ));
            }
        }

        if ($schemeMap->isEmpty()) {
            throw new CriticalException("No valid subscriptions found");
        }

        return $schemeMap;
    }
}