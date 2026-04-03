<?php

declare(strict_types=1);

namespace App\Infrastructure\OutboundTest\OutboundCountyCode\Geoip;

use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use InvalidArgumentException;
use MaxMind\Db\Reader\InvalidDatabaseException;

final class Reader
{
    private static ?\MaxMind\Db\Reader $reader = null;


    public function __construct(
        private readonly ConfigInstancePort $configInstancePort,
    )
    {
    }

    /**
     * @throws InvalidArgumentException
     * @throws InvalidDatabaseException
     */
    public function get(): \MaxMind\Db\Reader
    {
        if (self::$reader !== null) return self::$reader;

        self::$reader = new \MaxMind\Db\Reader($this->configInstancePort->get()->singBoxConfig->outboundTest->fetchIp->geoIpDatabase);

        return self::$reader;
    }
}