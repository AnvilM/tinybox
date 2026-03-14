<?php

declare(strict_types=1);

namespace App\Domain\Shared\Ports\Http;

use Closure;
use Exception;

interface HttpProt
{
    /**
     * Sends concurrent async http GET requests
     *
     * @param float $timeout Timeout in seconds
     * @param array $urls Array of urls ["http://", ...] or ["name" => "http://", ...]
     * @param ?Closure(string $responseBodyContent, $urlName): void $fulfilled Callback called if request is fulfilled
     * @param ?Closure(Exception $exception, $urlName): void $rejected Callback called if request is rejected
     *
     * @return void
     */
    public function getMultipleAsync(
        float    $timeout,
        array    $urls,
        ?Closure $fulfilled = null,
        ?Closure $rejected = null
    ): void;
}