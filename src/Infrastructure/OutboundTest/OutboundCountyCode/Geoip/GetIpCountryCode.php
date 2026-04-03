<?php

declare(strict_types=1);

namespace App\Infrastructure\OutboundTest\OutboundCountyCode\Geoip;

use BadMethodCallException;
use InvalidArgumentException;
use MaxMind\Db\Reader\InvalidDatabaseException;
use UnexpectedValueException;

final readonly class GetIpCountryCode
{

    public function __construct(
        private Reader $reader,
    )
    {
    }

    /**
     * @throws UnexpectedValueException If is code is not found
     */
    public function getCountryCode(string $ip): string
    {

        try {
            $ipRecord = $this->reader->get()->get($ip);
        } catch (InvalidArgumentException|InvalidDatabaseException|BadMethodCallException $e) {
            throw new UnexpectedValueException($e->getMessage());
        }

        if (!is_array($ipRecord) || !array_key_exists('country', $ipRecord) || !array_key_exists('iso_code', $ipRecord['country'])) {
            throw new UnexpectedValueException('Invalid IP address');
        }

        return $ipRecord['country']['iso_code'];

    }
}