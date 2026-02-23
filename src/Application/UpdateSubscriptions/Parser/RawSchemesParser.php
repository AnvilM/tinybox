<?php

declare(strict_types=1);

namespace App\Application\UpdateSubscriptions\Parser;

use App\Core\Scheme\VO\RawSchemeVO;
use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Ports\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\UpdateSubscriptionsLifecycle\Parser\RawSchemesParser\InvalidSchemeReporterEvent;
use Random\RandomException;


final readonly class RawSchemesParser
{
    public function __construct(
        private ReporterPort $reporterPort,
    )
    {
    }

    /**
     * Parses raw schemes string into array of rawSchemeVO
     *
     * @param string $rawSchemesString Schemes string separated by \n
     * @param string $subscriptionName Subscription name for reporter
     *
     * @return RawSchemeVO[] Array of rawSchemeVO
     *
     * @throws CriticalException Throws if unable to generate tag
     */
    public function parse(string $rawSchemesString, string $subscriptionName): array
    {
        $rawSchemesStringArray = array_filter(array_map('trim', explode("\n", $rawSchemesString)));

        $rawSchemeVOArray = [];

        foreach ($rawSchemesStringArray as $rawSchemeString) {
            $parsed = parse_url($rawSchemeString);
            if (!$parsed) {
                $this->reporterPort->notify(new InvalidSchemeReporterEvent($rawSchemeString, $subscriptionName));
                continue;
            }

            $queryParams = [];
            if (isset($parsed['query'])) {
                parse_str($parsed['query'], $queryParams);
            }
            try {
                $tag = $parsed['fragment'] ?? bin2hex(random_bytes(4));
            } catch (RandomException $e) {
                throw new CriticalException("Unable to parse scheme string", $e->getMessage());
            }

            $rawSchemeVOArray[] = new RawSchemeVO(
                $parsed['scheme'] ?? null,
                $tag ?? null,
                $parsed['user'] ?? null,
                $parsed['host'] ?? null,
                (int)$parsed['port'] ?? null,
                $queryParams['sni'] ?? null,
                $queryParams['pbk'] ?? null,
                $queryParams['sid'] ?? null,
                $queryParams['flow'] ?? null,
                $queryParams['fp'] ?? null,
            );
        }

        return $rawSchemeVOArray;
    }
}