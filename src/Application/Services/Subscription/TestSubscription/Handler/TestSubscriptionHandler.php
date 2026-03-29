<?php

declare(strict_types=1);

namespace App\Application\Services\Subscription\TestSubscription\Handler;

use App\Application\Services\Subscription\TestSubscription\Command\TestSubscriptionCommand;
use App\Application\Shared\Shared\Utils\OutboundTest\GetOutboundsLatency\GetOutboundsLatencyUseCase;
use App\Application\Shared\Subscription\UseCase\ReadSubscriptionsList\ReadSubscriptionsListUseCase;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Exception\UnsupportedOutboundTypeException;
use App\Domain\Outbound\Factory\OutboundFactory;
use App\Domain\Scheme\Exception\SchemeNotFoundException;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\VO\Config\SingBox\OutboundTest\Latency\LatencyTestMethod;
use App\Domain\Subscription\Exception\InvalidSubscriptionNameException;
use App\Domain\Subscription\Exception\SubscriptionNotFoundException;
use App\Domain\Subscription\VO\SubscriptionNameVO;
use InvalidArgumentException;
use Psl\Collection\MutableMap;

final readonly class TestSubscriptionHandler
{
    public function __construct(
        private GetOutboundsLatencyUseCase   $getOutboundsLatencyUseCase,
        private ConfigInstancePort           $configInstancePort,
        private ReadSubscriptionsListUseCase $readSubscriptionsListUseCase,
    )
    {
    }

    /**
     * @throws CriticalException
     */
    public function handle(TestSubscriptionCommand $command): MutableMap
    {
        /**
         * Read list of all saved subscriptions
         */
        $subscriptions = $this->readSubscriptionsListUseCase->handle();


        /**
         * Try to create subscription name
         */
        try {
            $subscriptionName = new SubscriptionNameVO($command->subscriptionName);
        } catch (InvalidSubscriptionNameException) {
            throw new CriticalException("Invalid subscription name provided");
        }


        /**
         * Try to find subscription with provided name
         */
        try {
            $subscription = $subscriptions->getSubscriptionByName($subscriptionName);
        } catch (SubscriptionNotFoundException) {
            throw new CriticalException("Subscription with name {$subscriptionName->getName()} not found");
        }

        /**
         * Check if subscription has schemes
         */
        if ($subscription->getSchemes()->isEmpty()) throw new CriticalException("Subscription  {$subscriptionName->getName()} has no schemes");


        /**
         * Create empty outbounds map
         */
        $outboundsMap = new OutboundMap();


        foreach ($subscription->getSchemes()->getMap() as $scheme) {
            /**
             * Try to create outbound from scheme and add it to outbounds map
             */
            try {
                $outboundsMap->add(OutboundFactory::fromScheme($scheme));
            } catch (OutboundAlreadyExistsException|InvalidArgumentException|UnsupportedOutboundTypeException $e) {
                echo $e->getMessage() . "\n";
                continue;
                // TODO: Add reporter event
            }
        }

        $map = new MutableMap([]);

        $res = $this->getOutboundsLatencyUseCase->handle(
            $outboundsMap,
            LatencyTestMethod::tryFrom($command->testMethod ?? '')
            ?? $this->configInstancePort->get()->singBoxConfig->outboundTest->latency->method);

        foreach ($res as $tag => $latency) {
            try {
                $map->add($subscription->getSchemes()->getByTag($tag)->getHash(), new MutableMap([])->add($tag, $latency));
            } catch (SchemeNotFoundException) {
                continue;
            }
        }

        return $map;
    }
}