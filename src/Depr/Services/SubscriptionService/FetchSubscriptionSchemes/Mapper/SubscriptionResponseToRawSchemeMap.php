<?php

declare(strict_types=1);

namespace App\Core\Services\SubscriptionService\FetchSubscriptionSchemes\Mapper;

use App\Core\Collections\RawScheme\Collection\RawSchemeCollection;
use App\Core\Collections\RawScheme\RawSchemeMap;
use App\Core\Entity\RawScheme;
use PHPUnit\Event\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

final readonly class SubscriptionResponseToRawSchemeMap
{
    /**
     * @param ResponseInterface $response
     * @param string $subscriptionName
     * @param callable(string $message, ?string $rawSchemeString): void $onError
     * @return RawSchemeMap
     */
    public function map(ResponseInterface $response, string $subscriptionName, callable $onError): RawSchemeMap
    {
        $responseContents = $response->getBody()->getContents();


        $decodedResponse = base64_decode($responseContents, true);

        if (!$decodedResponse) throw new InvalidArgumentException('Invalid base64 in response');

        $rawSchemeCollection = new RawSchemeCollection();

        foreach (explode(PHP_EOL, trim($decodedResponse)) as $rawSchemeString) {
            try {
                $rawSchemeCollection->add(new RawScheme($rawSchemeString));
            } catch (InvalidArgumentException $exception) {
                $onError($exception->getMessage(), $rawSchemeString);
            }
        }

        return new RawSchemeMap([
            $subscriptionName => $rawSchemeCollection
        ]);
    }
}