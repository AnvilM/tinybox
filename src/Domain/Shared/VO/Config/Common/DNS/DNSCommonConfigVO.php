<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Config\Common\DNS;

use App\Domain\Shared\Exception\Config\ConfigException;

final readonly class DNSCommonConfigVO
{
    /**
     * @param string[] $endpoints
     * @throws ConfigException
     */
    public function __construct(
        public int   $resolveTimeout,
        public array $endpoints
    )
    {
        if (count($this->endpoints) === 0) throw new ConfigException("DNS endpoints array is empty");
        foreach ($this->endpoints as $endpoint) {
            if (!is_string($endpoint)) {
                throw new ConfigException("DNS endpoints array must be string");
            }
        }
    }
}