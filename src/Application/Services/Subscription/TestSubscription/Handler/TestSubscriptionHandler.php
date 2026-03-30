<?php

declare(strict_types=1);

namespace App\Application\Services\Subscription\TestSubscription\Handler;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\Subscription\GetSubscriptionWithNameRepository;
use App\Application\Services\Subscription\TestSubscription\Command\TestSubscriptionCommand;
use App\Application\Shared\Utils\OutboundTest\GetOutboundsLatency\GetOutboundsLatencyUseCase;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Exception\UnsupportedOutboundTypeException;
use App\Domain\Outbound\Factory\OutboundFactory;
use App\Domain\Scheme\Exception\SchemeNotFoundException;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\VO\Config\SingBox\OutboundTest\Latency\LatencyTestMethod;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Subscription\Exception\SubscriptionNotFoundException;
use InvalidArgumentException;
use Psl\Collection\MutableMap;

final readonly class TestSubscriptionHandler
{
    public function __construct(
        private GetOutboundsLatencyUseCase        $getOutboundsLatencyUseCase,
        private ConfigInstancePort                $configInstancePort,
        private GetSubscriptionWithNameRepository $getSubscriptionWithNameRepository,
    )
    {
    }

    /**
     * @throws CriticalException
     */
    public function handle(TestSubscriptionCommand $command): MutableMap
    {
        /**
         * Try to get subscription with provided name
         */
        try {
            $subscription = $this->getSubscriptionWithNameRepository->get(
                new NonEmptyStringVO($command->subscriptionName)
            );
        } catch (InvalidArgumentException|UnableToGetListException|SubscriptionNotFoundException $e) {
            throw new CriticalException(match (true) {
                $e instanceof InvalidArgumentException => "Invalid subscription name: " . $command->subscriptionName,
                $e instanceof UnableToGetListException => "Unable to get subscriptions list",
                $e instanceof SubscriptionNotFoundException => "Subscription not found: " . $command->subscriptionName
            }, $e->getDebugMessage());
        }

        /**
         * Check if subscription has schemes
         */
        if ($subscription->getSchemes()->isEmpty()) throw new CriticalException("Subscription  {$subscription->getName()} has no schemes");


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

        /**
         * Create empty mutable map of schemeId => MutableMap<schemeTag: string, latency: int|null>
         */
        $map = new MutableMap([]);

        $res = $this->getOutboundsLatencyUseCase->handle(
            $outboundsMap,
            LatencyTestMethod::tryFrom($command->testMethod ?? '')
            ?? $this->configInstancePort->get()->singBoxConfig->outboundTest->latency->method
        );

        $resArray = $res->toArray();

        uasort($resArray, function ($a, $b) {
            if ($a === null) return 1;
            if ($b === null) return -1;
            return $a <=> $b;
        });

        foreach ($resArray as $tag => $latency) {
            try {
                $map->add(
                    $subscription->getSchemes()->getByTag($tag)->getHash(),
                    new MutableMap([])->add($tag, $latency)
                );
            } catch (SchemeNotFoundException) {
                continue;
                // TODO: Add reporter event
            }
        }

        return $map;
    }
}