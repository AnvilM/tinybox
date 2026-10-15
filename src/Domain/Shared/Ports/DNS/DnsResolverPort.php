<?php

declare(strict_types=1);

namespace App\Domain\Shared\Ports\DNS;

interface DnsResolverPort
{
    public function resolveDoH(string $host): ?string;
}