<?php

declare(strict_types=1);

namespace App\Application\Shared\Subscription\UseCase\FetchSchemes;

use App\Application\Shared\Common\Scheme\UseCase\CreateSchemeEntityFromStringUseCase;
use App\Application\Shared\Scheme\Exception\Shared\Parser\UnableToParseRawSchemeStringException;
use App\Application\Shared\Subscription\Exception\UseCase\FetchSchemes\NoValidSchemesFoundException;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use App\Domain\Scheme\Exception\UnsupportedSchemeType;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\HTTP\HttpException;
use App\Domain\Shared\Ports\Http\HttpPort;
use App\Domain\Subscription\Entity\Subscription;
use InvalidArgumentException;
use Psl\Encoding\Base64;
use Psl\Encoding\Exception\IncorrectPaddingException;
use Psl\Encoding\Exception\RangeException;
use RuntimeException;

final readonly class FetchSchemesUseCase
{
    public function __construct(
        private HttpPort                            $httpPort,
        private CreateSchemeEntityFromStringUseCase $createSchemeEntityFromStringUseCase,
    )
    {
    }

    /**
     * Fetch schemes and add them to provided subscription
     *
     * @param Subscription $subscription Subscription to fetch
     *
     * @return Subscription Subscription with schemes
     *
     * @throws CriticalException
     * @throws NoValidSchemesFoundException If no valid schemes found
     */
    public function handle(Subscription $subscription): Subscription
    {
        /**
         * Try to load schemes
         */
        try {
            $rawEncodedSchemesString = $this->httpPort->get(10.0, $subscription->getUrl())
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


        foreach ($rawSchemesStrings as $rawSchemeString) {
            /**
             * Try to create scheme entity from string and add it to scheme mao
             */
            try {
                $subscription->getSchemes()->add(
                    $this->createSchemeEntityFromStringUseCase->handle($rawSchemeString)
                );
            } catch (UnableToParseRawSchemeStringException|InvalidArgumentException|UnsupportedSchemeType|SchemeAlreadyExistsException) {
                continue;
                //TODO: add reporter event
            }
        }


        /**
         * Check if schemes map is not empty
         */
        if ($subscription->getSchemes()->getMap()->isEmpty()) throw new NoValidSchemesFoundException();


        /**
         * Return subscriptions
         */
        return $subscription;
    }
}