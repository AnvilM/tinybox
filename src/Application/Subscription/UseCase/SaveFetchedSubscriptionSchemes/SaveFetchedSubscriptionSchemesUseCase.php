<?php

declare(strict_types=1);

namespace App\Application\Subscription\UseCase\SaveFetchedSubscriptionSchemes;

use App\Application\Repository\Outbound\AddOutboundRepository;
use App\Application\Repository\Outbound\GetOutboundsListRepository;
use App\Application\Repository\Shared\Exception\UnableToGetListException;
use App\Application\Repository\Shared\Exception\UnableToSaveListException;
use App\Application\Repository\Subscription\AddSubscriptionRepository;
use App\Application\Subscription\Exception\UseCase\SaveFetchedSubscriptionSchemes\NoValidSchemesFoundException;
use App\Application\Subscription\UseCase\CreateOutboundsFromSchemes\CreateOutboundsFromSchemesUseCase;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Subscription\Entity\OutboundsSubscription;
use App\Domain\Subscription\Exception\SubscriptionAlreadyExistsException;
use App\Domain\Subscription\VO\SubscriptionURLVO;

final readonly class SaveFetchedSubscriptionSchemesUseCase
{
    public function __construct(
        private CreateOutboundsFromSchemesUseCase $createOutboundsFromSchemesUseCase,
        private AddOutboundRepository             $addOutboundRepository,
        private AddSubscriptionRepository         $addSubscriptionRepository,
        private GetOutboundsListRepository        $getOutboundsListRepository,
    )
    {
    }


    /**
     * Parse and save fetched subscription schemes and subscription
     *
     * Rules applied to the fetched outbounds:
     *  - outbounds with the same content (see {@see Outbound::equalsContent()}) are saved once, whatever their tags are.
     *    The comparison is strict: if any parameter differs (e.g. TLS fingerprint), outbounds are different;
     *  - outbounds with different content but the same tag are all saved: tags in a subscription must be unique
     *    (sing-box refuses duplicated tags), so the next ones get a numeric suffix: "tag #2", "tag #3", ...
     *
     * @param NonEmptyStringVO $subscriptionName Subscription name
     * @param SubscriptionURLVO $subscriptionUrl Subscription Url
     * @param string $fetchedSchemesString Fetched schemes as plain text
     *
     * @throws NoValidSchemesFoundException If valid schemes not found
     * @throws SubscriptionAlreadyExistsException If subscription with provided name or url already exist
     * @throws UnableToGetListException If unable to get list of subscriptions or outbounds
     * @throws UnableToSaveListException If unable to save subscriptions list or outbounds list
     */
    public function handle(NonEmptyStringVO $subscriptionName, SubscriptionURLVO $subscriptionUrl, string $fetchedSchemesString): void
    {

        /**
         * Parse schemes into unique outbounds
         */
        $outbounds = $this->createOutboundsFromSchemesUseCase->handle($fetchedSchemesString);


        foreach ($outbounds->getOutbounds() as $outbound) {

            /**
             * Find duplicate of each fetched outbounds
             */
            $duplicate = $this->getOutboundsListRepository->getOutboundsList()->getDuplicate($outbound);


            /**
             * Remove duplicate from fetched outbounds and add existed outbound
             */
            if ($duplicate instanceof Outbound) $outbounds
                ->remove($outbound)
                ->add($duplicate);


            /**
             * Add outbound to outbounds list if duplicates not found
             */
            else $this->addOutboundRepository->add($outbound);
        }


        /**
         * Add new subscription and save subscriptions list
         */
        $this->addSubscriptionRepository->add(new OutboundsSubscription(
            $subscriptionName,
            $subscriptionUrl,
            $outbounds
        ))->save();


        /**
         * Save outbounds list
         */
        $this->addOutboundRepository->save();
    }
}