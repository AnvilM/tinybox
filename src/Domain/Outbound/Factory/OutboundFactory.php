<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Factory;

use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Entity\TLS\Reality;
use App\Domain\Outbound\Entity\TLS\TLS;
use App\Domain\Outbound\Entity\TLS\UTLS;
use App\Domain\Scheme\Entity\Scheme;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\PortVO;
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
            new NonEmptyStringVO($scheme->getTag()),
            new NonEmptyStringVO($scheme->getServer()),
            new PortVO($scheme->getServerPort()),
            new NonEmptyStringVO($scheme->getUuid()),
            $scheme->getFlow() === null ? null : new NonEmptyStringVO($scheme->getFlow()),
            new TLS(
                new NonEmptyStringVO($scheme->getSni()),
                new Reality(
                    new NonEmptyStringVO($scheme->getPbk()),
                    new NonEmptyStringVO($scheme->getSid()),
                    true
                ),
                $scheme->getFp() ? new UTLS(
                    new NonEmptyStringVO($scheme->getFp()),
                    true
                ) : null,
                true
            )
        );
    }
}