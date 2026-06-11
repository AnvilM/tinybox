<?php

declare(strict_types=1);

namespace App\Application\Subscription\UseCase\SaveFetchedSubscriptionConfig;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Exception\Repository\Shared\UnableToSaveListException;
use App\Application\Exception\Services\Shared\FetchSchemes\NoValidSchemesFoundException;
use App\Application\Repository\Outbound\AddOutboundRepository;
use App\Application\Repository\Subscription\AddSubscriptionRepository;
use App\Application\Shared\Scheme\CreateSchemeEntityFromString\CreateSchemeEntityFromStringUseCase;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Subscription\Entity\ConfigSubscription;
use App\Domain\Subscription\Exception\SubscriptionAlreadyExistsException;
use App\Domain\Subscription\VO\SubscriptionNameVO;
use App\Domain\Subscription\VO\SubscriptionURLVO;
use InvalidArgumentException;

final readonly class SaveFetchedSubscriptionConfigUseCase
{
    public function __construct(
        private CreateSchemeEntityFromStringUseCase $createSchemeEntityFromStringUseCase,
        private AddOutboundRepository               $addOutboundRepository,
        private AddSubscriptionRepository           $addSubscriptionRepository,
    )
    {
    }


    /**
     * Save fetched subscription config and subscription
     *
     * @param SubscriptionNameVO $subscriptionName Subscription name
     * @param SubscriptionURLVO $subscriptionUrl Subscription Url
     * @param string $configString Fetched config as plain text
     *
     * @throws NoValidSchemesFoundException If valid schemes not found
     * @throws SubscriptionAlreadyExistsException If subscription with provided name or url already exist
     * @throws UnableToGetListException If unable to get list of subscriptions or outbounds
     * @throws UnableToSaveListException If unable to save subscriptions list or outbounds list
     * @throws InvalidArgumentException If config is invalid
     */
    public function handle(SubscriptionNameVO $subscriptionName, SubscriptionURLVO $subscriptionUrl, string $configString): void
    {
        /**
         * Try to add new subscription and save subscriptions list
         */
        $this->addSubscriptionRepository->add(new ConfigSubscription(
            $subscriptionName,
            $subscriptionUrl,
            new NonEmptyStringVO($configString)
        ))->save();
    }
}