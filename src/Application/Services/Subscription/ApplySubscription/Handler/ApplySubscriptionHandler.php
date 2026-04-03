<?php

declare(strict_types=1);

namespace App\Application\Services\Subscription\ApplySubscription\Handler;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\Subscription\GetSubscriptionListRepository;
use App\Application\Services\Subscription\ApplySubscription\Command\ApplySubscriptionCommand;
use App\Application\Services\Subscription\ApplySubscription\Exception\UnableToRestartSingBoxServiceException;
use App\Application\Shared\UseCase\CreateSingBoxConfig\CreateSingBoxConfigUseCase;
use App\Application\Shared\UseCase\RestartSingBoxService\RestartSingBoxServiceUseCase;
use App\Domain\Interface\Subscription\DetourProvider;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Exception\UnsupportedOutboundTypeException;
use App\Domain\Outbound\Factory\OutboundFactory;
use App\Domain\Outbound\Specification\OutboundCountryCodeSpecification;
use App\Domain\Outbound\Specification\OutboundTagSpecification;
use App\Domain\Scheme\Exception\SchemeNotFoundException;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\File\SaveFilePort;
use App\Domain\Shared\Ports\OutboundTest\OutboundCountyCode\OutboundCountyCodePort;
use App\Domain\Subscription\Exception\InvalidSubscriptionNameException;
use App\Domain\Subscription\Exception\SubscriptionNotFoundException;
use App\Domain\Subscription\VO\SubscriptionNameVO;
use InvalidArgumentException;
use Psl\Async\Exception\CompositeException;
use Psl\Collection\Vector;

final readonly class ApplySubscriptionHandler
{
    public function __construct(
        private GetSubscriptionListRepository $getSubscriptionListRepository,
        private CreateSingBoxConfigUseCase    $createSingBoxConfigUseCase,
        private SaveFilePort                  $saveFilePort,
        private ConfigInstancePort            $configInstancePort,
        private RestartSingBoxServiceUseCase  $restartSingBoxServiceUseCase,
        private OutboundCountyCodePort        $outboundCountyCodePort,

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
         * Try to create outbound to exclude country except
         */
        if ($command->denyCountry) try {
            $outboundsCountryCodes = $this->outboundCountyCodePort->getCountryCodes($outboundsMap, $command->excludeCountryForce);


            if ($command->excludeCountryExcept) {
                $outboundToExcludeCountryExcept = new Vector([OutboundFactory::fromScheme(
                    $subscription->getSchemes()->getById($command->excludeCountryExcept)
                )]);
            }

            $excludeCountrySpecification = new OutboundCountryCodeSpecification(
                $outboundsCountryCodes,
                new Vector([$command->denyCountry]),
                $outboundToExcludeCountryExcept ?? null,
            );
        } catch (SchemeNotFoundException $e) {
            throw new CriticalException("Scheme with id $command->excludeCountryExcept not found", $e->getDebugMessage());
        } catch (UnsupportedOutboundTypeException|InvalidArgumentException $e) {
            throw new CriticalException("Cant create exclude country except outbound from provided scheme id", $e->getDebugMessage());
        } catch (CompositeException $e) {
            throw new CriticalException("Cant get outbounds ip's", $e->getMessage());
        }


        /**
         * Try to create outbound to exclude
         */
        if ($command->exclude) try {
            $outboundToExclude = OutboundFactory::fromScheme(
                $subscription->getSchemes()->getById($command->exclude)
            );

            $excludeOutboundSpecification = new OutboundTagSpecification(
                new Vector([$outboundToExclude->getTagString()])
            );
        } catch (SchemeNotFoundException $e) {
            throw new CriticalException("Scheme with id $command->exclude not found", $e->getDebugMessage());
        } catch (UnsupportedOutboundTypeException|InvalidArgumentException $e) {
            throw new CriticalException("Cant create exclude outbound from provided scheme id", $e->getDebugMessage());
        }


        /**
         * Filter outbounds
         */
        $outboundsMap = $outboundsMap->filter(new Vector([
            $excludeCountrySpecification ?? null,
            $excludeOutboundSpecification ?? null,
        ])->filter(fn(mixed $spec) => $spec !== null));


        /**
         * Create sing-box config from outbounds map
         */
        $singBoxConfig = $this->createSingBoxConfigUseCase->handle(
            $outboundsMap
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