<?php

declare(strict_types=1);

namespace App\Application\Services\Subscription\TestSubscription\Handler;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Outbound\DTO\OutboundLatencyDTO;
use App\Application\Repository\Subscription\GetSubscriptionWithNameRepository;
use App\Application\Services\Subscription\TestSubscription\Command\TestSubscriptionCommand;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Exception\UnsupportedOutboundTypeException;
use App\Domain\Outbound\Factory\FromScheme\FromSchemeOutboundFactory;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\VO\Config\SingBox\OutboundTest\Latency\LatencyTestMethod;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Subscription\Exception\SubscriptionNotFoundException;
use App\Infrastructure\OutboundTest\OutboundLatency\Exception\UnableToGetLatencyException;
use App\Infrastructure\OutboundTest\OutboundLatency\OutboundLatency;
use App\Infrastructure\OutboundTest\Shared\CreateOutboundTestSingBoxConfig\Exception\CreateOutboundTestSingBoxConfigException;
use InvalidArgumentException;
use Psl\Collection\MutableMap;

final readonly class TestSubscriptionHandler
{
    public function __construct(
        private OutboundLatency                   $outboundLatency,
        private ConfigInstancePort                $configInstancePort,
        private GetSubscriptionWithNameRepository $getSubscriptionWithNameRepository,
    )
    {
    }

    /**
     * @return MutableMap<OutboundLatencyDTO>
     *
     * @throws CriticalException
     *
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
        if ($subscription->getOutbounds()->isEmpty()) throw new CriticalException("Subscription  {$subscription->getNameString()} has no schemes");


        /**
         * Create empty outbounds map
         */
        $outboundsMap = new OutboundMap();


        foreach ($subscription->getOutbounds()->getMap() as $scheme) {
            /**
             * Try to create outbound from scheme and add it to outbounds map
             */
            try {
                $outboundsMap->add(FromSchemeOutboundFactory::fromScheme($scheme, $outboundsMap->count()));
            } catch (OutboundAlreadyExistsException|InvalidArgumentException|UnsupportedOutboundTypeException $e) {
                echo $e->getMessage() . "\n";
                continue;
                // TODO: Add reporter event
            }
        }

        $map = new MutableMap([]);

        try {
            $result = $this->outboundLatency->getOutboundsLatency(
                $outboundsMap,
                LatencyTestMethod::tryFrom($command->testMethod ?? '')
                ?? $this->configInstancePort->get()->singBoxConfig->outboundTest->latency->method
            );
        } catch (UnableToSaveFileException|CreateOutboundTestSingBoxConfigException|UnableToGetLatencyException $e) {
            throw new CriticalException("Unable to test subscription outbounds");
        }

        foreach ($result as $res) {
            $m = new MutableMap([]);
            $m->add($res->outbound->getTagString(), $res->latency);
            $map->add($subscription->getOutbounds()->getByTag(
                $res->outbound->getTagString()
            )->getHash(), $m);
        }

        return $map;
    }
}