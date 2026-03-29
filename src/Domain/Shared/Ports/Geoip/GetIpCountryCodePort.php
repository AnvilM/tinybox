<?php

declare(strict_types=1);

namespace App\Domain\Shared\Ports\Geoip;

use UnexpectedValueException;

interface GetIpCountryCodePort
{
    /**
     * @throws UnexpectedValueException If is code is not found
     */
    public function getCountryCode(string $ip): string;
}