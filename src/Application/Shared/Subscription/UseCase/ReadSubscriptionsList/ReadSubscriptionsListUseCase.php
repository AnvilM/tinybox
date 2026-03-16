<?php

declare(strict_types=1);

namespace App\Application\Shared\Subscription\UseCase\ReadSubscriptionsList;

use App\Application\Shared\Scheme\UseCase\ReadSchemesList\ReadSchemesListUseCase;
use App\Application\Shared\Subscription\Exception\Shared\Validator\InvalidSubscriptionsListFormatException;
use App\Application\Shared\Subscription\Shared\File\ReadSubscriptions;
use App\Application\Shared\Subscription\Shared\Validator\SubscriptionsListFormatValidator;
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
     * @throws CriticalException
     */
    public function handle(): SubscriptionsMap
    {
        try {
            $rawSubscriptionsListArray = $this->readSubscriptions->read();

            $this->subscriptionsListFormatValidator->validate($rawSubscriptionsListArray);


            /** @var array<array{name: string, url: string, schemes: string[]}> $rawSubscriptionsListArray */

        } catch (UnableToReadFileException $e) {
            throw new CriticalException("Unable to read subscriptions list", $e->getMessage());
        } catch (UnableToDecodeJsonException|InvalidSubscriptionsListFormatException $e) {
            throw new CriticalException("Invalid subscriptions list format", $e->getMessage());
        }

        $schemes = $this->readSchemesListUseCase->handle();

        $subscriptions = new SubscriptionsMap();

        foreach ($rawSubscriptionsListArray as $rawSubscription) {
            $subscriptionSchemes = new UniqueSchemesMap();

            foreach ($rawSubscription['schemes'] as $rawSubscriptionScheme) {
                try {
                    $scheme = $schemes->getById($rawSubscriptionScheme);
                } catch (SchemeNotFoundException) {
                    continue;
                    //TODO: Add reporter event
                }

                try {
                    $subscriptionSchemes->add($scheme);
                } catch (SchemeAlreadyExistsException) {
                    continue;
                    //TODO: Add reporter event
                }

            }

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