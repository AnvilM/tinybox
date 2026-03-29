<?php

declare(strict_types=1);

namespace App\Application\Services\Subscription\ApplySubscription\Handler;

use App\Application\Services\Subscription\ApplySubscription\Command\ApplySubscriptionCommand;
use App\Application\Services\Subscription\ApplySubscription\Exception\UnableToRestartSingBoxServiceException;
use App\Application\Shared\Shared\Utils\OutboundTest\GetIpCountyCode\GetIpCountryCodesMapUseCase;
use App\Application\Shared\Shared\Utils\UseCase\CreateSingBoxConfig\CreateSingBoxConfigUseCase;
use App\Application\Shared\Shared\Utils\UseCase\RestartSingBoxService\RestartSingBoxServiceUseCase;
use App\Application\Shared\Subscription\UseCase\ReadSubscriptionsList\ReadSubscriptionsListUseCase;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Exception\UnsupportedOutboundTypeException;
use App\Domain\Outbound\Factory\OutboundFactory;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\File\SaveFilePort;
use App\Domain\Subscription\Exception\InvalidSubscriptionNameException;
use App\Domain\Subscription\Exception\SubscriptionNotFoundException;
use App\Domain\Subscription\VO\SubscriptionNameVO;
use InvalidArgumentException;

final readonly class ApplySubscriptionHandler
{
    public function __construct(
        private ReadSubscriptionsListUseCase $readSubscriptionsListUseCase,
        private CreateSingBoxConfigUseCase   $createSingBoxConfigUseCase,
        private SaveFilePort                 $saveFilePort,
        private ConfigInstancePort           $configInstancePort,
        private RestartSingBoxServiceUseCase $restartSingBoxServiceUseCase,
        private GetIpCountryCodesMapUseCase  $getIpCountryCodesMapUseCase,

    )
    {
    }

    /**
     * @throws CriticalException
     */
    public function handle(ApplySubscriptionCommand $command): void
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


        /**
         *  Filter outbounds if needed
         */
        if ($command->denyCountry != null) {
            $outboundsCountyCodes = $this->getIpCountryCodesMapUseCase->getCountryCodesMap($outboundsMap);

            foreach ($outboundsCountyCodes as $outboundTag => $countyCode) {
                if ($countyCode == $command->denyCountry) $outboundsMap->removeWithTag($outboundTag);
            }

            if ($outboundsMap->isEmpty()) throw new CriticalException("No valid outbounds found");
        }


        /**
         * Create sing-box config from outbounds map
         */
        $singBoxConfig = $this->createSingBoxConfigUseCase->handle($outboundsMap);


        /**
         * Try to save config file
         */
        try {
            $this->saveFilePort->save(
                $this->configInstancePort->get()->singBoxConfig->defaultConfigPath,
                $singBoxConfig
            );
        } catch (UnableToSaveFileException) {
            throw new CriticalException("Unable to save the configuration file");
        }

        /**
         * Try to restart sing box service
         */
        try {
            $this->restartSingBoxServiceUseCase->handle();
        } catch (UnableToRestartSingBoxServiceException) {
            throw new CriticalException("Unable to restart sing-box service");
        }
    }
}