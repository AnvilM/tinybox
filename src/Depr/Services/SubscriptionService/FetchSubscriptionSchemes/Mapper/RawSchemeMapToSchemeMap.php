<?php

declare(strict_types=1);

namespace App\Core\Services\SubscriptionService\FetchSubscriptionSchemes\Mapper;

use App\Core\Collections\RawScheme\Collection\RawSchemeCollection;
use App\Core\Collections\RawScheme\RawSchemeMap;
use App\Core\Collections\Scheme\Collection\SchemeCollection;
use App\Core\Collections\Scheme\SchemeMap;
use App\Core\Entity\Scheme;
use App\Core\Exceptions\ApplicationException;
use App\Core\VO\SchemeTypeVO;
use App\Infrastructure\Shared\CLI\Output;
use League\CLImate\CLImate;
use Random\RandomException;
use Throwable;

final readonly class RawSchemeMapToSchemeMap
{
    public function __construct(
        private CLImate $climate,
    )
    {
    }

    /**
     * @param RawSchemeMap $rawSchemeMap
     * @param callable(string $message): void $onError
     * @return SchemeMap
     * @throws ApplicationException
     */
    public function map(RawSchemeMap $rawSchemeMap, callable $onError): SchemeMap
    {
        $schemeMap = new SchemeMap();

        foreach ($rawSchemeMap as $subscriptionName => $rawScheme) {
            $schemeCollection = new SchemeCollection();

            foreach ($rawScheme as $scheme) {
                try {
                    $schemeCollection->add(new Scheme(
                        SchemeTypeVO::from($scheme->type),
                        $scheme->uuid,
                        $scheme->server,
                        $scheme->port,
                        $scheme?->queryParams['sni'] ?? null,
                        $scheme?->queryParams['pbk'] ?? null,
                        $scheme?->queryParams['sid'] ?? null,
                        $scheme?->queryParams['flow'] ?? null,
                        $scheme?->queryParams['fp'] ?? null,
                        $scheme->tag
                    ));
                } catch (Throwable $e) {
                    $onError($e->getMessage());
                }
            }
        }
    }

    /**
     * @param RawSchemeCollection $schemes
     * @param string $subscriptionName
     * @return SchemeCollection
     */
    private function buildSchemeCollection(RawSchemeCollection $schemes, string $subscriptionName): SchemeCollection
    {
        $collection = new SchemeCollection();

        foreach ($schemes as $rawScheme) {
            $scheme = $this->createSchemeFromString($rawScheme, $subscriptionName);

            if ($scheme !== null) {
                $collection->add($scheme);
            }
        }

        return $collection;
    }

    private function createSchemeFromString(string $rawScheme, string $subscriptionName): ?Scheme
    {
        $parts = parse_url($rawScheme);

        if ($parts === false || empty($parts['scheme'])) {
            $this->logInvalidScheme(null, $subscriptionName, $rawScheme);
            return null;
        }

        $queryParams = $this->extractQueryParams($parts);

        try {
            return new Scheme(
                SchemeTypeVO::from($parts['scheme']),
                $parts['user'] ?? null,
                $parts['host'] ?? null,
                isset($parts['port']) ? (int)$parts['port'] : null,
                $queryParams['sni'] ?? null,
                $queryParams['pbk'] ?? null,
                $queryParams['sid'] ?? null,
                $queryParams['flow'] ?? null,
                $queryParams['fp'] ?? null,
                $this->resolveFragment($parts)
            );
        } catch (Throwable) {
            $this->logInvalidScheme($parts['fragment'] ?? null, $subscriptionName, $rawScheme);
            return null;
        }
    }

    private function logInvalidScheme(?string $fragment, string $subscriptionName, string $rawScheme): void
    {
        Output::out(
            sprintf(
                "<yellow>[~] Invalid<bold>%s </bold>scheme in <bold>%s</bold></yellow>",
                $fragment ? ' ' . $fragment : '',
                $subscriptionName
            ),
            trim($rawScheme),
            $this->climate->to('error')
        )->br();
    }

    private function extractQueryParams(array $parts): array
    {
        if (!isset($parts['query'])) {
            return [];
        }

        parse_str($parts['query'], $params);

        return is_array($params) ? $params : [];
    }

    /**
     * @throws RandomException
     */
    private function resolveFragment(array $parts): string
    {
        if (!empty($parts['fragment'])) {
            return $parts['fragment'];
        }

        $scheme = strtoupper($parts['scheme'] ?? 'UNKNOWN');

        return sprintf('%s-%s', $scheme, bin2hex(random_bytes(4)));
    }
}