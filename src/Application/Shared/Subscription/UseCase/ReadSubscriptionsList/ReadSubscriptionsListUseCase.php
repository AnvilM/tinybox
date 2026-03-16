<?php

declare(strict_types=1);

namespace App\Application\Shared\Subscription\UseCase\ReadSubscriptionsList;

use App\Application\Shared\Scheme\UseCase\ReadSchemesList\ReadSchemesListUseCase;
use App\Application\Shared\Subscription\Exception\Shared\Validator\InvalidSubscriptionsListFormatException;
use App\Application\Shared\Subscription\Shared\File\ReadSubscriptions;
use App\Application\Shared\Subscription\Shared\Validator\SubscriptionsListFormatValidator;
use App\Domain\Config\Exception\InvalidSchemeIdException;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use App\Domain\Shared\VO\Shared\SchemeIdVO;
use App\Domain\Shared\VO\Shared\SchemesIdsVO;
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
            $schemesIdsVo = new SchemesIdsVO();

            foreach ($rawSubscription['schemes'] as $rawSubscriptionScheme) {
                try {
                    $schemeIdVo = new SchemeIdVO($rawSubscriptionScheme);
                } catch (InvalidSchemeIdException) {
                    continue;
                    //TODO: Add reporter event
                }

                if (!$schemes->containsSchemeId($schemeIdVo->getSchemeId())) continue;
                //TODO: Add reporter event

                try {
                    $schemesIdsVo->add($schemeIdVo);
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
                        $schemesIdsVo
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