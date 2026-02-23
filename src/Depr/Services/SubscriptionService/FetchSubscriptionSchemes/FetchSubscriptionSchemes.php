<?php

declare(strict_types=1);

namespace App\Core\Services\SubscriptionService\FetchSubscriptionSchemes;

use App\Core\Collections\RawScheme\RawSchemeMap;
use App\Core\Collections\Scheme\SchemeMap;
use App\Core\Exceptions\ApplicationException;
use App\Core\Services\SubscriptionService\FetchSubscriptionSchemes\Mapper\RawSchemeMapToSchemeMap;
use App\Core\Services\SubscriptionService\FetchSubscriptionSchemes\Mapper\SubscriptionResponseToRawSchemeMap;
use App\Infrastructure\Shared\CLI\Output;
use Exception;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use League\CLImate\CLImate;

final readonly class FetchSubscriptionSchemes
{
    public function __construct(
        private CLImate                            $climate,
        private RawSchemeMapToSchemeMap            $subscriptionSchemeMapper,
        private SubscriptionResponseToRawSchemeMap $subscriptionResponseToRawSchemeMap
    )
    {
    }

    /**
     * Load subscriptions from urls
     *
     * @param array<string, string> $subscriptionList Subscription list
     * @return SchemeMap SchemeMap
     * @throws ApplicationException
     */
    public function fetchSubscriptionSchemes(array $subscriptionList): SchemeMap
    {
        // $client = new Client(['timeout' => 5.0]);

        $requests = function () use ($subscriptionList) {
            return array_map(function ($subscription) {
                return new Request('GET', $subscription);
            }, $subscriptionList);
        };

        $rawSchemeMap = new RawSchemeMap();
        new Pool($client, $requests(), [
            'concurrency' => count($subscriptionList),
            'fulfilled' => function (Response $response, $subscriptionName) use (&$rawSchemeMap) {

                $rawSchemeMap[] = $this->subscriptionResponseToRawSchemeMap->map($response, $subscriptionName,
                    function (string $message, ?string $rawSchemeString) use ($subscriptionName) {
                        Output::out(
                            "<yellow>[~] Invalid scheme in <bold>$subscriptionName</bold></yellow>",
                            $message . ': ' . trim($rawSchemeString),
                            $this->climate->to('error')
                        )->br();
                    }
                );

            },
            'rejected' => function (Exception $exception, $name) {
                Output::out(
                    "<yellow>[~] <bold>$name</bold> -> Cannot fetch subscription</yellow>",
                    trim($exception->getMessage()),
                    $this->climate->to('error')
                )->br();
            },
        ])->promise()->wait();

        if ($rawSchemeMap->isEmpty()) {
            throw new ApplicationException('No valid subscriptions found');
        }

        return $this->subscriptionSchemeMapper->map($rawSchemeMap);
    }
}