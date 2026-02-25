<?php

declare(strict_types=1);

namespace App\Application\UpdateSubscriptions\Fetch;

use App\Core\Domain\Subscription\Collection\SubscriptionCollection;
use App\Core\Domain\Subscription\Entity\Subscription;
use App\Core\Shared\Ports\Http\HttpProt;
use App\Core\Shared\Ports\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\UpdateSubscriptionsLifecycle\Fetch\FetchSubscriptions\InvalidBase64ReporterEvent;
use App\Core\Shared\ReporterEvent\Events\UpdateSubscriptionsLifecycle\Fetch\FetchSubscriptions\StartFetchingSubscriptionsReporterEvent;
use App\Core\Shared\ReporterEvent\Events\UpdateSubscriptionsLifecycle\Fetch\FetchSubscriptions\SubscriptionFetchingFailedReporterEvent;
use App\Core\Shared\ReporterEvent\Events\UpdateSubscriptionsLifecycle\Fetch\FetchSubscriptions\SubscriptionSuccessfullyFetchedReporterEvent;
use Exception;

final readonly class FetchSubscriptions
{
    public function __construct(
        private HttpProt     $httpProt,
        private ReporterPort $reporterPort,
    )
    {
    }

    /**
     * Load raw schemes string from subscriptions url
     *
     * @param SubscriptionCollection $subscriptions Subscriptions collection
     *
     * @return array<string, string> Raw schemes string array ["subscriptionName" => "rawSchemesString"]
     */
    public function load(SubscriptionCollection $subscriptions): array
    {

        $this->reporterPort->notify(new StartFetchingSubscriptionsReporterEvent($subscriptions));

        $rawSchemesArray = [];

        $urls = [];
        foreach ($subscriptions as $subscription) {
            $urls[$subscription->name] = $subscription->url;
        }

        $this->httpProt->getMultipleAsync(
            5.0, $urls,
            function (string $responseBodyContent, string $subscriptionName) use (&$rawSchemesArray, $urls) {
                $decodedSchemesString = base64_decode($responseBodyContent, true);

                if ($decodedSchemesString === false)
                    $this->reporterPort->notify(new InvalidBase64ReporterEvent(new Subscription($subscriptionName, $urls[$subscriptionName])));

                else $rawSchemesArray[$subscriptionName] = $decodedSchemesString;

                $this->reporterPort->notify(new SubscriptionSuccessfullyFetchedReporterEvent(
                    new Subscription($subscriptionName, $urls[$subscriptionName])
                ));
            },
            fn(Exception $exception, string $subscriptionName) => $this->reporterPort->notify(new SubscriptionFetchingFailedReporterEvent(
                $exception,
                new Subscription($subscriptionName, $urls[$subscriptionName])
            ))
        );

        return $rawSchemesArray;
    }
}