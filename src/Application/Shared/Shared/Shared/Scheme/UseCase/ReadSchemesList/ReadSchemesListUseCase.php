<?php

declare(strict_types=1);

namespace App\Application\Shared\Shared\Shared\Scheme\UseCase\ReadSchemesList;

use App\Application\Shared\Scheme\Exception\Shared\Parser\UnableToParseRawSchemeStringException;
use App\Application\Shared\Scheme\Exception\Shared\Validator\InvalidSchemesListFormatException;
use App\Application\Shared\Scheme\Shared\File\ReadSchemes;
use App\Application\Shared\Scheme\Shared\Validator\SchemesListFormatValidator;
use App\Application\Shared\Shared\Shared\Scheme\UseCase\CreateSchemeEntityFromString\CreateSchemeEntityFromStringUseCase;
use App\Domain\Scheme\Collection\SchemeMap;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use App\Domain\Scheme\Exception\UnsupportedSchemeType;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use App\Domain\Shared\ReporterEvent\Events\AddScheme\Handler\AddSchemeHandler\InvalidSchemeReporterEvent;
use InvalidArgumentException;

final readonly class ReadSchemesListUseCase
{
    public function __construct(
        private ReadSchemes                         $readSchemes,
        private CreateSchemeEntityFromStringUseCase $createSchemeEntityFromStringUseCase,
        private SchemesListFormatValidator          $schemesListFormatValidator,
        private ReporterPort                        $reporterPort,
    )
    {
    }


    /**
     * Read schemes list from file
     *
     * @return SchemeMap Map of scheme entity
     *
     * @throws CriticalException
     */
    public function handle(): SchemeMap
    {
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
            throw new CriticalException($e instanceof UnableToReadFileException
                ? "Unable to read schemes list"
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

        return $schemes;
    }
}