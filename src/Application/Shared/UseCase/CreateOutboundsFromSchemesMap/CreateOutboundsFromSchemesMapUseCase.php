<?php

declare(strict_types=1);

namespace App\Application\Shared\UseCase\CreateOutboundsFromSchemesMap;

use App\Application\Shared\DTO\UseCase\CreateOutboundsFromSchemesMap\CreateOutboundsFromSchemesMapDTO;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Exception\UnsupportedOutboundTypeException;
use App\Domain\Outbound\Factory\FromScheme\FromSchemeOutboundFactory;
use InvalidArgumentException;

final readonly class CreateOutboundsFromSchemesMapUseCase
{
    public function handle(CreateOutboundsFromSchemesMapDTO $DTO): OutboundMap
    {
        /**
         * Create empty outbounds map
         */
        $outboundsMap = new OutboundMap();


        foreach ($DTO->schemeMap->getMap() as $scheme) {
            /**
             * Try to create outbound from scheme and add it to outbounds map
             */
            try {
                /**
                 * Create an outbound
                 */
                $outbound = FromSchemeOutboundFactory::fromScheme($scheme, $outboundsMap->count());


                /**
                 * Add created outbound to outbounds map
                 */
                $outboundsMap->add($outbound);
            } catch (OutboundAlreadyExistsException|InvalidArgumentException|UnsupportedOutboundTypeException) {
                continue;
                // TODO: Add reporter event
            }

        }

        return $outboundsMap;
    }
}