<?php

declare(strict_types=1);

namespace App\Application\Repository\Subscription\Shared\Builder\Builder;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\Outbound\GetOutboundsListRepository;
use App\Domain\Outbound\Collection\UniqueOutboundsMap;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Exception\OutboundNotFoundException;
use App\Domain\Subscription\VO\RawSubscription\RawOutboundsSubscriptionVO;
use InvalidArgumentException;
use Throwable;

final readonly class RawOutboundsSubscriptionVOBuilder
{
    public function __construct(
        private GetOutboundsListRepository $getOutboundsListRepository,
    )
    {
    }


    /**
     * Parse outbounds subscription as JSON decoded array to raw outbounds subscription value object
     *
     * @param array $jsonDecodedOutboundsSubscription Outbounds subscription as JSON decoded array
     *
     * @return RawOutboundsSubscriptionVO Raw outbounds subscription value object
     *
     * @throws InvalidArgumentException
     * @throws UnableToGetListException
     */
    public function handle(array $jsonDecodedOutboundsSubscription): RawOutboundsSubscriptionVO
    {
        /**
         * Get subscription outbounds
         */
        $outboundsIds = $jsonDecodedOutboundsSubscription['outbounds'] ?? [];


        /**
         * Get all outbounds
         */
        $outbounds = $this->getOutboundsListRepository->getOutboundsList();


        /**
         * Create empty subscription outbounds map
         */
        $subscriptionOutbounds = new UniqueOutboundsMap();


        /**
         * Create subscription outbounds from outbounds ids
         */
        foreach ($outboundsIds as $outboundId) {
            try {
                $subscriptionOutbounds->add($outbounds->getWithId($outboundId));
            } catch (OutboundAlreadyExistsException|OutboundNotFoundException) {
                continue;
                //TODO: Report
            }
        }


        try {
            return new RawOutboundsSubscriptionVO(
                $jsonDecodedOutboundsSubscription['name'],
                $jsonDecodedOutboundsSubscription['url'],
                $jsonDecodedOutboundsSubscription['type'],
                $subscriptionOutbounds,
            );
        } catch (Throwable) {
            throw new InvalidArgumentException();
        }
    }
}