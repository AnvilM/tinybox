<?php

declare(strict_types=1);

namespace App\Application\Repository\Outbound\Shared;

use App\Application\Exception\Repository\Outbound\Validator\InvalidOutboundsListFormatException;
use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Exception\Repository\Shared\UnableToSaveListException;
use App\Application\Repository\Outbound\Shared\File\ReadOutbounds;
use App\Application\Repository\Outbound\Shared\File\WriteOutbounds;
use App\Application\Repository\Outbound\Shared\Validator\OutboundsListFormatValidator;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Exception\UnsupportedOutboundTypeException;
use App\Domain\Outbound\Factory\FromRawOutbound\FromRawOutboundFactory;
use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;
use App\Domain\Shared\Ports\Outbound\Parser\RawOutboundParserPort;
use InvalidArgumentException;

class OutboundRepository
{
    private static ?OutboundMap $outboundsMap = null;

    public function __construct(
        private readonly ReadOutbounds                $readOutbounds,
        private readonly OutboundsListFormatValidator $outboundsListFormatValidator,
        private readonly WriteOutbounds               $writeOutbounds,
        private readonly RawOutboundParserPort        $rawOutboundParserPort,
        private readonly FromRawOutboundFactory       $fromRawOutboundFactory,
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
            $rawOutboundsList = $this->readOutbounds->read();

            /**
             * Validate outbounds
             */
            $this->outboundsListFormatValidator->validate($rawOutboundsList);


            /** @var array[] $rawOutboundsList */

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


        foreach ($rawOutboundsList as $id => $rawOutbound) {
            /**
             * Try to create and add outbound to outbounds map
             */
            try {
                $outbounds->add($this->fromRawOutboundFactory->fromRawOutboundVO(
                    $this->rawOutboundParserPort->parse($rawOutbound), $id
                ));
            } catch (UnsupportedOutboundTypeException|OutboundAlreadyExistsException|InvalidArgumentException) {
                continue;
                // TODO: Add reporter event
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

        try {
            $this->writeOutbounds->write(self::$outboundsMap);
        } catch (UnableToSaveFileException|UnableToEncodeJsonException $e) {
            throw new UnableToSaveListException($e->getMessage(), $e->getDebugMessage());
        }

        return self::$outboundsMap;
    }
}