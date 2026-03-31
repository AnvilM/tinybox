<?php

declare(strict_types=1);

namespace App\Application\Services\Subscription\ApplySubscription\Handler;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\Subscription\GetSubscriptionListRepository;
use App\Application\Services\Subscription\ApplySubscription\Command\ApplySubscriptionCommand;
use App\Application\Services\Subscription\ApplySubscription\CreateSingBoxConfig\CreateSingBoxConfigUseCase;
use App\Application\Services\Subscription\ApplySubscription\Exception\UnableToRestartSingBoxServiceException;
use App\Application\Services\Subscription\ApplySubscription\RestartSingBoxService\RestartSingBoxServiceUseCase;
use App\Application\Shared\Utils\OutboundTest\GetIpCountyCode\GetIpCountryCodesMapUseCase;
use App\Domain\Interface\Subscription\DetourProvider;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Exception\UnsupportedOutboundTypeException;
use App\Domain\Outbound\Factory\OutboundFactory;
use App\Domain\Scheme\Exception\SchemeNotFoundException;
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
        private GetSubscriptionListRepository $getSubscriptionListRepository,
        private CreateSingBoxConfigUseCase    $createSingBoxConfigUseCase,
        private SaveFilePort                  $saveFilePort,
        private ConfigInstancePort            $configInstancePort,
        private RestartSingBoxServiceUseCase  $restartSingBoxServiceUseCase,
        private GetIpCountryCodesMapUseCase   $getIpCountryCodesMapUseCase,

    )
    {
    }

    /**
     * @throws CriticalException
     */
    public function handle(ApplySubscriptionCommand $command): void
    {
        /**
         * Try to create subscription name
         */
        try {
            $subscriptionName = new SubscriptionNameVO($command->subscriptionName);
        } catch (InvalidSubscriptionNameException) {
            throw new CriticalException("Invalid subscription name provided", $command->subscriptionName);
        }


        /**
         * Try to get subscription with provided name
         */
        try {
            $subscription = $this->getSubscriptionListRepository->getSubscriptionsList()->getSubscriptionByName($subscriptionName);
        } catch (UnableToGetListException|SubscriptionNotFoundException $e) {
            throw new CriticalException($e->getMessage(), $e->getDebugMessage());
        }


        /**
         * Check if subscription has schemes
         */
        if ($subscription->getSchemes()->isEmpty()) throw new CriticalException("Subscription  {$subscription->getName()} has no schemes");


        /**
         * Try to create outbound to urltest exclude
         */
        if ($command->urltestExclude) try {
            $outboundToUrltestExclude = OutboundFactory::fromScheme(
                $subscription->getSchemes()->getById($command->urltestExclude)
            );
        } catch (SchemeNotFoundException $e) {
            throw new CriticalException("Scheme with id $command->urltestExclude not found", $e->getDebugMessage());
        } catch (UnsupportedOutboundTypeException|InvalidArgumentException $e) {
            throw new CriticalException("Cant create urltest exclude outbound from provided scheme id", $e->getDebugMessage());
        }


        /**
         * Try to create outbound to exclude
         */
        if ($command->exclude) try {
            $outboundToExclude = OutboundFactory::fromScheme(
                $subscription->getSchemes()->getById($command->exclude)
            );
        } catch (SchemeNotFoundException $e) {
            throw new CriticalException("Scheme with id $command->exclude not found", $e->getDebugMessage());
        } catch (UnsupportedOutboundTypeException|InvalidArgumentException $e) {
            throw new CriticalException("Cant create exclude outbound from provided scheme id", $e->getDebugMessage());
        }


        /**
         * Try to create outbound from provided detour scheme id
         */
        if ($command->defaultDetour) try {
            $detourOutbound = OutboundFactory::fromScheme(
                $subscription->getSchemes()->getById($command->defaultDetour)
            );
        } catch (SchemeNotFoundException $e) {
            throw new CriticalException("Scheme with id $command->defaultDetour not found", $e->getDebugMessage());
        } catch (UnsupportedOutboundTypeException|InvalidArgumentException $e) {
            throw new CriticalException("Cant create detour outbound from provided scheme id", $e->getDebugMessage());
        }

        /**
         * Create empty outbounds map
         */
        $outboundsMap = new OutboundMap();


        foreach ($subscription->getSchemes()->getMap() as $scheme) {
            if (isset($outboundToExclude) && $scheme->getHash() === $outboundToExclude) continue;

            /**
             * Try to create outbound from scheme and add it to outbounds map
             */
            try {
                /**
                 * Create an outbound
                 */
                $outbound = OutboundFactory::fromScheme($scheme);


                /**
                 * Set for created outbound default detour outbound
                 */
                if (isset($detourOutbound) && $command->defaultDetour !== null && $outbound instanceof DetourProvider
                    && $outbound->getTagString() !== $detourOutbound->getTagString())
                    $outbound->setDetour($detourOutbound);


                /**
                 * Add created outbound to outbounds map
                 */
                $outboundsMap->add($outbound);
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
        $singBoxConfig = $this->createSingBoxConfigUseCase->handle(
            $outboundsMap, $command->urltest, $outboundToUrltestExclude ?? null
        );


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