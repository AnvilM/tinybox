<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Config\Common\DNS;

final readonly class DNSCommonConfigVO
{
    /**
     * @param string[] $endpoints
     */
    public function __construct(
        public int   $resolveTimeout,
        public array $endpoints
    )
    {
    }
}