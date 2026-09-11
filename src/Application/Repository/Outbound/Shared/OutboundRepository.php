<?php

declare(strict_types=1);

namespace App\Application\Repository\Outbound\Shared;

use App\Application\Exception\Repository\Outbound\Validator\InvalidOutboundsListFormatException;
use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Exception\Repository\Shared\UnableToSaveListException;
use App\Application\Exception\Shared\Scheme\CreateSchemeEntityFromString\UnableToParseRawSchemeStringException;
use App\Application\Outbound\Mapper\ToSchemeString\ToSchemeStringOutboundMapper;
use App\Application\Outbound\UseCase\CreateOutboundFromScheme\CreateOutboundFromSchemeUseCase;
use App\Application\Repository\Outbound\Shared\File\ReadOutbounds;
use App\Application\Repository\Outbound\Shared\File\WriteOutbounds;
use App\Application\Repository\Outbound\Shared\Validator\OutboundsListFormatValidator;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Exception\UnsupportedProtocolException;
use App\Domain\Outbound\Exception\UnsupportedSecurityException;
use App\Domain\Outbound\Exception\UnsupportedTransportException;
use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;
use InvalidArgumentException;

class OutboundRepository
{
    private static ?OutboundMap $outboundsMap = null;

    public function __construct(
        private readonly ReadOutbounds                   $readOutbounds,
        private readonly OutboundsListFormatValidator    $outboundsListFormatValidator,
        private readonly WriteOutbounds                  $writeOutbounds,
        private readonly CreateOutboundFromSchemeUseCase $createOutboundFromSchemeUseCase,
        private readonly ToSchemeStringOutboundMapper    $toSchemeStringOutboundMapper,

    )
    {

    }

    /**
     * Get map of all outbounds
     *
     * @return OutboundMap Outbound map
     *
     * @throws UnableToGetListException If unable to read file or outbounds file is invalid format
     */
    protected function getOutboundsList(): OutboundMap
    {
        /**
         * Check if outbounds map is already exist
         */
        if (self::$outboundsMap !== null) return self::$outboundsMap;


        try {
            /**
             * Read outbounds
             */
            $rawSchemesList = $this->readOutbounds->read();

            /**
             * Validate outbounds
             */
            $this->outboundsListFormatValidator->validate($rawSchemesList);


            /** @var string[] $rawSchemesList */

        } catch (UnableToReadFileException|UnableToDecodeJsonException|InvalidOutboundsListFormatException $e) {
            throw new UnableToGetListException($e instanceof UnableToReadFileException
                ? "Unable to read outbounds list file"
                : "Invalid outbounds list format",
                $e->getMessage()
            );
        }


        /**
         * Create empty outbounds map
         */
        $outbounds = new OutboundMap();


        foreach ($rawSchemesList as $rawScheme) {
            /**
             * Try to create and add outbound to outbounds map
             */
            try {
                $outbounds->add(
                    $this->createOutboundFromSchemeUseCase->handle($rawScheme)
                );
            } catch (OutboundAlreadyExistsException|UnableToParseRawSchemeStringException|UnsupportedProtocolException|UnsupportedTransportException|UnsupportedSecurityException|InvalidArgumentException $e) {
                continue;
                // TODO: add reporter event
            }
        }


        /**
         * Update outbounds map
         */
        self::$outboundsMap = $outbounds;

        return $outbounds;
    }


    /**
     * Save current outbounds list to file
     *
     * @throws UnableToSaveListException If unable to write file, or no outbounds loaded
     */
    protected function save(): OutboundMap
    {
        if (self::$outboundsMap === null) throw new UnableToSaveListException(
            "No outbounds list available"
        );


        /**
         * Try to create scheme strings from outbounds
         */

        $outbounds = [];

        foreach (self::$outboundsMap->getOutbounds() as $outbound) {
            try {
                $outbounds[] = $this->toSchemeStringOutboundMapper->map($outbound);
            } catch (InvalidArgumentException) {
                continue;
                // TODO: Add reporter event
            }
        }

        try {
            $this->writeOutbounds->write($outbounds);
        } catch (UnableToSaveFileException|UnableToEncodeJsonException $e) {
            throw new UnableToSaveListException($e->getMessage(), $e->getDebugMessage());
        }

        return self::$outboundsMap;
    }
}