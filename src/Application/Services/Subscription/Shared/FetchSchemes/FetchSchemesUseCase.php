<?php

declare(strict_types=1);

namespace App\Application\Services\Subscription\Shared\FetchSchemes;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Exception\Repository\Shared\UnableToSaveListException;
use App\Application\Exception\Services\Shared\FetchSchemes\NoValidSchemesFoundException;
use App\Application\Exception\Shared\Scheme\CreateSchemeEntityFromString\UnableToParseRawSchemeStringException;
use App\Application\Repository\Outbound\AddOutboundRepository;
use App\Application\Shared\Scheme\CreateSchemeEntityFromString\CreateSchemeEntityFromStringUseCase;
use App\Domain\Outbound\Collection\UniqueOutboundsMap;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Factory\FromScheme\FromSchemeOutboundFactory;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\HTTP\HttpException;
use App\Domain\Shared\Ports\Http\HttpPort;
use App\Domain\Subscription\VO\SubscriptionURLVO;
use InvalidArgumentException;
use Psl\Encoding;
use Psl\Encoding\Base64;
use Psl\Encoding\Exception\IncorrectPaddingException;
use Psl\Encoding\Exception\RangeException;
use RuntimeException;
use Throwable;

final readonly class FetchSchemesUseCase
{
    public function __construct(
        private HttpPort                            $httpPort,
        private CreateSchemeEntityFromStringUseCase $createSchemeEntityFromStringUseCase,
        private AddOutboundRepository               $addOutboundRepository,
    )
    {
    }

    /**
     * Fetch outbounds from provided url
     *
     * @param SubscriptionURLVO $subscriptionUrl Subscription url to fetch outbounds
     *
     * @return UniqueOutboundsMap Unique outbounds map
     *
     * @throws CriticalException
     * @throws NoValidSchemesFoundException If no valid outbounds found
     */
    public function handle(SubscriptionURLVO $subscriptionUrl): UniqueOutboundsMap
    {
        /**
         * Try to load outbounds
         */
        try {
            $rawEncodedOutboundsString = $this->httpPort->get(10.0, $subscriptionUrl->getUrl())
                ->getBody()
                ->getContents();
        } catch (RuntimeException $e) {
            throw new CriticalException("Unable to read response", $e->getMessage());
        } catch (HttpException $e) {
            throw new CriticalException("Unable to send request", $e->getDebugMessage());
        }


        /**
         * Try to decode response
         */
        try {
            $rawOutboundsString = Base64\decode($rawEncodedOutboundsString);
        } catch (IncorrectPaddingException|RangeException $e) {
            throw new CriticalException("Invalid response", $e->getMessage());
        }


        /**
         * Explode raw outbounds string by \n
         */
        $rawOutboundsStrings = explode("\n", $rawOutboundsString);


        /**
         * Create empty unique outbounds map
         */
        $outbounds = new UniqueOutboundsMap();

        foreach ($rawOutboundsStrings as $rawOutboundString) {
            /**
             * Try to create outbound entity from string and add it to outbound mao
             */
            try {
                $outbounds->add(
                    FromSchemeOutboundFactory::fromScheme(
                        $this->createSchemeEntityFromStringUseCase->handle($rawOutboundString),
                        $outbounds->count()
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


        /**
         * Add outbounds to outbounds list
         */
        foreach ($outbounds->getMap() as $outbound) {
            try {
                $this->addOutboundRepository->add($outbound);
            } catch (OutboundAlreadyExistsException) {
                continue;
                // TODO: Add reporter event
            } catch (UnableToGetListException $e) {
                throw new CriticalException("Unable to get outbounds list", $e->getDebugMessage());
            }
        }


        /**
         * Try to save outbounds list
         */
        try {
            $this->addOutboundRepository->save();
        } catch (UnableToSaveListException $e) {
            throw new CriticalException("Unable to save outbounds list", $e->getDebugMessage());
        }

        /**
         * Return subscriptions
         */
        return $outbounds;
    }
}