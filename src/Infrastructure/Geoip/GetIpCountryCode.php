<?php

declare(strict_types=1);

namespace App\Infrastructure\Geoip;

use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\Geoip\GetIpCountryCodePort;
use BadMethodCallException;
use InvalidArgumentException;
use MaxMind\Db\Reader;
use UnexpectedValueException;

final readonly class GetIpCountryCode implements GetIpCountryCodePort
{

    public function __construct(
        private ConfigInstancePort $configInstancePort
    )
    {
    }

    /**
     * @throws UnexpectedValueException If is code is not found
     */
    public function getCountryCode(string $ip): string
    {

        try {
            $ipRecord = new Reader($this->configInstancePort->get()->singBoxConfig->outboundTest->fetchIp->geoIpDatabase)
                ->get($ip);
        } catch (InvalidArgumentException|Reader\InvalidDatabaseException|BadMethodCallException $e) {
            throw new UnexpectedValueException($e->getMessage());
        }

        if (!is_array($ipRecord) || !array_key_exists('country', $ipRecord) || !array_key_exists('iso_code', $ipRecord['country'])) {
            throw new UnexpectedValueException('Invalid IP address');
        }

        return $ipRecord['country']['iso_code'];

    }
}