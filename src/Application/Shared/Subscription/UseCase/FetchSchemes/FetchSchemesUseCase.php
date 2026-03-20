<?php

declare(strict_types=1);

namespace App\Application\Shared\Subscription\UseCase\FetchSchemes;

use App\Application\Shared\Scheme\Exception\Shared\Parser\UnableToParseRawSchemeStringException;
use App\Application\Shared\Shared\Scheme\UseCase\WriteSchemeMap\WriteSchemeMapUseCase;
use App\Application\Shared\Shared\Shared\Scheme\UseCase\CreateSchemeEntityFromString\CreateSchemeEntityFromStringUseCase;
use App\Application\Shared\Subscription\Exception\UseCase\FetchSchemes\NoValidSchemesFoundException;
use App\Domain\Scheme\Collection\UniqueSchemesMap;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use App\Domain\Scheme\Exception\UnsupportedSchemeType;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\HTTP\HttpException;
use App\Domain\Shared\Ports\Http\HttpPort;
use App\Domain\Subscription\VO\SubscriptionURLVO;
use InvalidArgumentException;
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
        private WriteSchemeMapUseCase               $writeSchemeMapUseCase,
    )
    {
    }

    /**
     * Fetch schemes from provided url
     *
     * @param SubscriptionURLVO $subscriptionUrl Subscription url to fetch schemes
     *
     * @return UniqueSchemesMap Unique schemes map
     *
     * @throws CriticalException
     * @throws NoValidSchemesFoundException If no valid schemes found
     */
    public function handle(SubscriptionURLVO $subscriptionUrl): UniqueSchemesMap
    {
        /**
         * Try to load schemes
         */
        try {
            $rawEncodedSchemesString = $this->httpPort->get(10.0, $subscriptionUrl->getUrl())
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
            $rawSchemesString = Base64\decode($rawEncodedSchemesString);
        } catch (IncorrectPaddingException|RangeException $e) {
            throw new CriticalException("Invalid response", $e->getMessage());
        }


        /**
         * Explode raw schemes string by \n
         */
        $rawSchemesStrings = explode("\n", $rawSchemesString);


        /**
         * Create empty unique schemes map
         */
        $schemes = new UniqueSchemesMap();

        foreach ($rawSchemesStrings as $rawSchemeString) {
            /**
             * Try to create scheme entity from string and add it to scheme mao
             */
            try {
                $schemes->add(
                    $this->createSchemeEntityFromStringUseCase->handle($rawSchemeString)
                );
            } catch (UnableToParseRawSchemeStringException|InvalidArgumentException|UnsupportedSchemeType|SchemeAlreadyExistsException) {
                continue;
                //TODO: add reporter event
            } catch (Throwable) {
                continue;
            }
        }


        /**
         * Check if schemes map is not empty
         */
        if ($schemes->getMap()->isEmpty()) throw new NoValidSchemesFoundException();


        /**
         * Write schemes to file
         */
        $this->writeSchemeMapUseCase->handle($schemes);


        /**
         * Return subscriptions
         */
        return $schemes;
    }
}