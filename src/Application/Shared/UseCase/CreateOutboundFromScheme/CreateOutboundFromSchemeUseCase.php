<?php

declare(strict_types=1);

namespace App\Application\Shared\UseCase\CreateOutboundFromScheme;

use App\Application\Shared\DTO\UseCase\CreateOutboundFromScheme\CreateOutboundFromSchemeDTO;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Exception\UnsupportedOutboundTypeException;
use App\Domain\Outbound\Factory\OutboundFactory;
use App\Domain\Shared\Exception\CriticalException;
use InvalidArgumentException;

final readonly class CreateOutboundFromSchemeUseCase
{
    /**
     * @throws CriticalException
     */
    public function handle(CreateOutboundFromSchemeDTO $DTO): Outbound
    {
        try {
            return OutboundFactory::fromScheme(
                $DTO->scheme
            );
        } catch (UnsupportedOutboundTypeException|InvalidArgumentException) {
            throw new CriticalException("Unable to create outbound from scheme");
        }
    }
}