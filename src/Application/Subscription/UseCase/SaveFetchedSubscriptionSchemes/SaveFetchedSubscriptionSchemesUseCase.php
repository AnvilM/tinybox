<?php

declare(strict_types=1);

namespace App\Application\Subscription\UseCase\SaveFetchedSubscriptionSchemes;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Exception\Repository\Shared\UnableToSaveListException;
use App\Application\Exception\Services\Shared\FetchSchemes\NoValidSchemesFoundException;
use App\Application\Exception\Shared\Scheme\CreateSchemeEntityFromString\UnableToParseRawSchemeStringException;
use App\Application\Repository\Outbound\AddOutboundRepository;
use App\Application\Repository\Outbound\GetOutboundsListRepository;
use App\Application\Repository\Subscription\AddSubscriptionRepository;
use App\Application\Shared\Scheme\CreateSchemeEntityFromString\CreateSchemeEntityFromStringUseCase;
use App\Domain\Outbound\Collection\UniqueTagAndContentOutboundsMap;
use App\Domain\Outbound\Collection\UniqueTagOutboundsMap;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Factory\FromScheme\FromSchemeOutboundFactory;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Subscription\Entity\OutboundsSubscription;
use App\Domain\Subscription\Exception\SubscriptionAlreadyExistsException;
use App\Domain\Subscription\VO\SubscriptionURLVO;
use InvalidArgumentException;
use Throwable;

final readonly class SaveFetchedSubscriptionSchemesUseCase
{
    public function __construct(
        private CreateSchemeEntityFromStringUseCase $createSchemeEntityFromStringUseCase,
        private AddOutboundRepository               $addOutboundRepository,
        private AddSubscriptionRepository           $addSubscriptionRepository,
        private GetOutboundsListRepository          $getOutboundsListRepository
    )
    {
    }


    /**
     * Parse and save fetched subscription schemes and subscription
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
    public function handle(NonEmptyStringVO $subscriptionName, SubscriptionURLVO $subscriptionUrl, string $fetchedSchemesString, bool $skipDuplicates): void
    {

        /**
         * Explode raw schemes string by \n
         */
        $schemesStrings = explode("\n", $fetchedSchemesString);


        /**
         * Create empty unique outbounds map
         */
        $outbounds = $skipDuplicates ? new UniqueTagAndContentOutboundsMap() : new UniqueTagOutboundsMap();


        foreach ($schemesStrings as $schemeString) {
            /**
             * Try to create outbound entity from string and add it to outbound mao
             */
            try {
                $outbounds->add(
                    FromSchemeOutboundFactory::fromScheme(
                        $this->createSchemeEntityFromStringUseCase->handle($schemeString),
                    )
                );
            } catch (UnableToParseRawSchemeStringException|InvalidArgumentException|OutboundAlreadyExistsException) {
                continue;
                //TODO: add reporter event
            } catch (Throwable) {
                continue;
            }
        }


        /**
         * Check if outbounds map is not empty
         */
        if ($outbounds->getMap()->isEmpty()) throw new NoValidSchemesFoundException();


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