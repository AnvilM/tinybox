<?php

declare(strict_types=1);

namespace App\Application\Repository\Scheme\Shared;

use App\Application\Exception\Repository\Scheme\UnableToGetSchemesListException;
use App\Application\Exception\Repository\Shared\UnableToSaveListException;
use App\Application\Repository\Scheme\Shared\File\ReadSchemes;
use App\Application\Repository\Scheme\Shared\File\WriteSchemes;
use App\Application\Repository\Scheme\Shared\Validator\SchemesListFormatValidator;
use App\Application\Shared\Scheme\Exception\Shared\Parser\UnableToParseRawSchemeStringException;
use App\Application\Shared\Scheme\Exception\Shared\Validator\InvalidSchemesListFormatException;
use App\Application\Shared\Shared\Shared\Scheme\Shared\UseCase\CreateSchemeEntityFromString\CreateSchemeEntityFromStringUseCase;
use App\Domain\Scheme\Collection\SchemeMap;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use App\Domain\Scheme\Exception\UnsupportedSchemeType;
use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use App\Domain\Shared\ReporterEvent\Events\AddScheme\Handler\AddSchemeHandler\InvalidSchemeReporterEvent;
use InvalidArgumentException;

class SchemeRepository
{
    private static ?SchemeMap $schemesMap = null;

    public function __construct(
        private readonly ReadSchemes                         $readSchemes,
        private readonly SchemesListFormatValidator          $schemesListFormatValidator,
        private readonly CreateSchemeEntityFromStringUseCase $createSchemeEntityFromStringUseCase,
        private readonly ReporterPort                        $reporterPort,
        private readonly WriteSchemes                        $writeSchemes,
    )
    {

    }

    /**
     * Get map of all schemes
     *
     * @return SchemeMap Scheme map
     *
     * @throws UnableToGetSchemesListException If unable to read file or schemes file is invalid format
     */
    protected function getSchemesList(): SchemeMap
    {
        /**
         * Check if schemes map is already exist
         */
        if (self::$schemesMap !== null) return self::$schemesMap;


        try {
            /**
             * Read schemes
             */
            $rawSchemesList = $this->readSchemes->read();

            /**
             * Validate schemes
             */
            $this->schemesListFormatValidator->validate($rawSchemesList);


            /** @var string[] $rawSchemesList */

        } catch (UnableToReadFileException|UnableToDecodeJsonException|InvalidSchemesListFormatException $e) {
            throw new UnableToGetSchemesListException($e instanceof UnableToReadFileException
                ? "Unable to read schemes list file"
                : "Invalid schemes list format",
                $e->getMessage()
            );
        }


        /**
         * Create empty schemes map
         */
        $schemes = new SchemeMap();


        foreach ($rawSchemesList as $rawSchemeString) {
            /**
             * Try to create and add scheme to schemes map
             */
            try {
                $schemes->add($this->createSchemeEntityFromStringUseCase->handle($rawSchemeString));
            } catch (UnsupportedSchemeType|UnableToParseRawSchemeStringException|InvalidArgumentException) {
                $this->reporterPort->notify(new InvalidSchemeReporterEvent($rawSchemeString));
                continue;
            } catch (SchemeAlreadyExistsException) {
                // TODO: Add reporter event
                continue;
            }
        }


        /**
         * Update schemes map
         */
        self::$schemesMap = $schemes;

        return $schemes;
    }


    /**
     * Save current schemes list to file
     *
     * @throws UnableToSaveListException If unable to write file, or no schemes loaded
     */
    protected function save(): SchemeMap
    {
        if (self::$schemesMap === null) throw new UnableToSaveListException(
            "No schemes list available"
        );

        try {
            $this->writeSchemes->write(self::$schemesMap);
        } catch (UnableToSaveFileException|UnableToEncodeJsonException $e) {
            throw new UnableToSaveListException($e->getMessage(), $e->getDebugMessage());
        }

        return self::$schemesMap;
    }
}