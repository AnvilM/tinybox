<?php

declare(strict_types=1);

namespace App\Domain\Subscription\VO;

use App\Domain\Subscription\Exception\InvalidSubscriptionURLException;

final readonly class SubscriptionURLVO
{
    private string $url;

    /**
     * Constructor
     *
     * @param string $url Subscription url
     *
     * @throws InvalidSubscriptionURLException
     */
    public function __construct(string $url)
    {
        $url = trim($url);

        if ($url === '') {
            throw new InvalidSubscriptionURLException('URL cannot be empty');
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidSubscriptionURLException("Invalid URL: {$url}");
        }

        $parts = parse_url($url);

        if (!isset($parts['scheme']) || !in_array($parts['scheme'], ['http', 'https'], true)) {
            throw new InvalidSubscriptionURLException('URL must use http or https scheme');
        }

        if (!isset($parts['host']) || $parts['host'] === '') {
            throw new InvalidSubscriptionURLException('URL must contain a host');
        }

        $this->url = $url;
    }


    /**
     * Get subscription url
     *
     * @return string Subscription url
     */
    public function getUrl(): string
    {
        return $this->url;
    }
}