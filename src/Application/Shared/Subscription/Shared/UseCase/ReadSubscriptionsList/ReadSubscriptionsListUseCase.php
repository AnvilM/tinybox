<?php

declare(strict_types=1);

namespace App\Application\Shared\Subscription\Shared\UseCase\ReadSubscriptionsList;

use App\Application\Shared\Shared\Shared\Scheme\UseCase\ReadSchemesList\ReadSchemesListUseCase;
use App\Application\Shared\Subscription\Exception\Shared\Validator\InvalidSubscriptionsListFormatException;
use App\Application\Shared\Subscription\Shared\Shared\File\ReadSubscriptions;
use App\Application\Shared\Subscription\Shared\Shared\Validator\SubscriptionsListFormatValidator;
use App\Domain\Scheme\Collection\UniqueSchemesMap;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use App\Domain\Scheme\Exception\SchemeNotFoundException;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use App\Domain\Subscription\Collection\SubscriptionsMap;
use App\Domain\Subscription\Entity\Subscription;
use App\Domain\Subscription\Exception\InvalidSubscriptionNameException;
use App\Domain\Subscription\Exception\InvalidSubscriptionURLException;
use App\Domain\Subscription\Exception\SubscriptionAlreadyExistsException;
use App\Domain\Subscription\VO\SubscriptionNameVO;
use App\Domain\Subscription\VO\SubscriptionURLVO;

final readonly class ReadSubscriptionsListUseCase
{
    public function __construct(
        private ReadSubscriptions                $readSubscriptions,
        private SubscriptionsListFormatValidator $subscriptionsListFormatValidator,
        private ReadSchemesListUseCase           $readSchemesListUseCase,
    )
    {
    }

    /**
     * Read subscriptions list from file
     *
     * @return SubscriptionsMap Map of subscription entity
     *
     * @throws CriticalException
     */
    public function handle(): SubscriptionsMap
    {
        try {
            /**
             * Read subscriptions list
             */
            $rawSubscriptionsList = $this->readSubscriptions->read();


            /**
             * Validate subscriptions list
             */
            $this->subscriptionsListFormatValidator->validate($rawSubscriptionsList);


            /** @var array<array{name: string, url: string, schemes: string[]}> $rawSubscriptionsList */

        } catch (UnableToReadFileException|UnableToDecodeJsonException|InvalidSubscriptionsListFormatException $e) {
            throw new CriticalException($e instanceof UnableToReadFileException
                ? "Unable to read subscriptions list"
                : "Invalid subscriptions list format",
                $e->getMessage()
            );
        }


        /**
         * Read schemes
         */
        $schemes = $this->readSchemesListUseCase->handle();


        /**
         * Create empty subscriptions map
         */
        $subscriptions = new SubscriptionsMap();

        foreach ($rawSubscriptionsList as $rawSubscription) {
            /**
             * Create empty subscription schemes map
             */
            $subscriptionSchemes = new UniqueSchemesMap();


            foreach ($rawSubscription['schemes'] as $rawSubscriptionScheme) {
                /**
                 * Try to find scheme with specific id
                 */
                try {
                    $scheme = $schemes->getById($rawSubscriptionScheme);
                } catch (SchemeNotFoundException) {
                    continue;
                    //TODO: Add reporter event
                }


                /**
                 * Try to add found scheme to subscription schemes map
                 */
                try {
                    $subscriptionSchemes->add($scheme);
                } catch (SchemeAlreadyExistsException) {
                    continue;
                    //TODO: Add reporter event
                }

            }


            /**
             * Try to add subscription to subscriptions map
             */
            try {
                $subscriptions->add(
                    new Subscription(
                        new SubscriptionNameVO($rawSubscription['name']),
                        new SubscriptionURLVO($rawSubscription['url']),
                        $subscriptionSchemes
                    )
                );
            } catch (SubscriptionAlreadyExistsException|InvalidSubscriptionNameException|InvalidSubscriptionURLException) {
                continue;
                //TODO: Add reporter event
            }
        }

        return $subscriptions;
    }
}