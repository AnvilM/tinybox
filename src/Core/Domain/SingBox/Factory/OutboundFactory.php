<?php

declare(strict_types=1);

namespace App\Core\Domain\SingBox\Factory;

use App\Core\Domain\Scheme\Entity\Scheme;
use App\Core\Domain\SingBox\Entity\Outbound\Outbound;
use App\Core\Domain\SingBox\Entity\TLS\Reality;
use App\Core\Domain\SingBox\Entity\TLS\TLS;
use App\Core\Domain\SingBox\Entity\TLS\UTLS;
use InvalidArgumentException;

final readonly class OutboundFactory
{
    /**
     * Creates an Outbound entity from Scheme entity
     *
     * @param Scheme $scheme Scheme entity
     *
     * @return Outbound The created Outbound entity
     *
     * @throws InvalidArgumentException If required fields are missing or empty
     */
    public static function fromScheme(Scheme $scheme): Outbound
    {
        return new Outbound(
            $scheme->getType(),
            $scheme->getTag(),
            $scheme->getServer(),
            $scheme->getServerPort(),
            $scheme->getUuid(),
            $scheme->getFlow(),
            new TLS(
                $scheme->getSni(),
                new Reality(
                    $scheme->getPbk(),
                    $scheme->getSid()
                ),
                $scheme->getFp() ? new UTLS(
                    $scheme->getFp()
                ) : null,
            )
        );
    }
}