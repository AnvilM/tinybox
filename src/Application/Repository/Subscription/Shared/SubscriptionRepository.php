<?php

declare(strict_types=1);

namespace App\Application\Repository\Subscription\Shared;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Exception\Repository\Shared\UnableToSaveListException;
use App\Application\Exception\Repository\Subscription\Validator\InvalidSubscriptionsListFormatException;
use App\Application\Repository\Subscription\Shared\Builder\RawSubscriptionVOBuilder;
use App\Application\Repository\Subscription\Shared\File\ReadSubscriptions;
use App\Application\Repository\Subscription\Shared\File\WriteSubscriptions;
use App\Application\Repository\Subscription\Shared\Validator\SubscriptionsListFormatValidator;
use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;
use App\Domain\Subscription\Collection\SubscriptionsMap;
use App\Domain\Subscription\Exception\InvalidSubscriptionURLException;
use App\Domain\Subscription\Exception\SubscriptionAlreadyExistsException;
use App\Domain\Subscription\Exception\UnsupportedSubscriptionTypeException;
use App\Domain\Subscription\Factory\FromRawSubscription\FromRawSubscriptionFactory;
use InvalidArgumentException;

class SubscriptionRepository
{
    private static ?SubscriptionsMap $subscriptionsMap = null;


    public function __construct(
        private readonly ReadSubscriptions                $readSubscriptions,
        private readonly SubscriptionsListFormatValidator $subscriptionsListFormatValidator,
        private readonly WriteSubscriptions               $writeSubscriptions,
        private readonly RawSubscriptionVOBuilder         $rawSubscriptionVOBuilder,
        private readonly FromRawSubscriptionFactory       $fromRawSubscriptionFactory,
    )
    {
    }

    /**
     * Get subscriptions list
     *
     * @return SubscriptionsMap list
     *
     * @throws UnableToGetListException If unable to read subscriptions file or outbounds file
     */
    protected function getSubscriptionsList(): SubscriptionsMap
    {
        if (self::$subscriptionsMap !== null) return self::$subscriptionsMap;

        try {
            /**
             * Read subscriptions list
             */
            $rawSubscriptionsList = $this->readSubscriptions->read();


            /**
             * Validate subscriptions list
             */
            $this->subscriptionsListFormatValidator->validate($rawSubscriptionsList);


            /** @var array<array{name: string, url: string, outbounds: string[]}> $rawSubscriptionsList */

        } catch (UnableToReadFileException|UnableToDecodeJsonException|InvalidSubscriptionsListFormatException $e) {
            throw new UnableToGetListException($e instanceof UnableToReadFileException
                ? "Unable to read subscriptions list"
                : "Invalid subscriptions list format",
                $e->getMessage()
            );
        }


        /**
         * Create empty subscriptions map
         */
        $subscriptions = new SubscriptionsMap();

        foreach ($rawSubscriptionsList as $rawSubscription) {
            /**
             * Try to add subscription to subscriptions map
             */
            try {
                $subscriptions->add(
                    $this->fromRawSubscriptionFactory->fromRawSubscriptionVO(
                        $this->rawSubscriptionVOBuilder->build($rawSubscription)
                    )
                );
            } catch (SubscriptionAlreadyExistsException|InvalidSubscriptionURLException|UnsupportedSubscriptionTypeException|InvalidArgumentException) {
                continue;
                //TODO: Add reporter event
            }
        }

        self::$subscriptionsMap = $subscriptions;

        return $subscriptions;
    }

    /**
     * Save current subscriptions list to file
     *
     * @throws UnableToSaveListException If unable to write file, or no subscriptions loaded
     */
    protected function save(): SubscriptionsMap
    {
        if (self::$subscriptionsMap === null) throw new UnableToSaveListException(
            "No subscriptions list available"
        );

        try {
            $this->writeSubscriptions->write(self::$subscriptionsMap);
        } catch (UnableToSaveFileException|UnableToEncodeJsonException $e) {
            throw new UnableToSaveListException($e->getMessage(), $e->getDebugMessage());
        }

        return self::$subscriptionsMap;
    }
}