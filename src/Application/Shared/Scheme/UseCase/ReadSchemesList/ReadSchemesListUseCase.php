<?php

declare(strict_types=1);

namespace App\Application\Shared\Scheme\UseCase\ReadSchemesList;

use App\Application\Shared\Scheme\Exception\Shared\Parser\UnableToParseRawSchemeStringException;
use App\Application\Shared\Scheme\Exception\Shared\Validator\InvalidSchemesListFormatException;
use App\Application\Shared\Scheme\Shared\File\ReadSchemes;
use App\Application\Shared\Scheme\Shared\Parser\RawSchemeParser;
use App\Application\Shared\Scheme\Shared\Validator\SchemesListFormatValidator;
use App\Domain\Scheme\Collection\SchemeMap;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use App\Domain\Scheme\Exception\UnsupportedSchemeType;
use App\Domain\Scheme\Factory\SchemeFactory;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use App\Domain\Shared\ReporterEvent\Events\AddScheme\Handler\AddSchemeHandler\InvalidSchemeReporterEvent;
use InvalidArgumentException;

final readonly class ReadSchemesListUseCase
{
    public function __construct(
        private ReadSchemes                $readSchemes,
        private RawSchemeParser            $rawSchemeParser,
        private SchemesListFormatValidator $schemesListFormatValidator,
        private ReporterPort               $reporterPort,
    )
    {
    }


    /**
     * Read schemes list from file
     *
     * @return SchemeMap Scheme map from file
     *
     * @throws CriticalException
     */
    public function handle(): SchemeMap
    {
        try {
            $rawSchemesStringArray = $this->readSchemes->read();
        } catch (UnableToReadFileException $e) {
            throw new CriticalException("Unable to read schemes list", $e->getMessage());
        } catch (UnableToDecodeJsonException $e) {
            throw new CriticalException("Invalid schemes list format", $e->getMessage());
        }

        try {
            $this->schemesListFormatValidator->validate($rawSchemesStringArray);
        } catch (InvalidSchemesListFormatException) {
            throw new CriticalException("Invalid schemes list format");
        }


        $schemes = new SchemeMap();

        foreach ($rawSchemesStringArray as $rawSchemeString) {

            try {
                $schemes->add(SchemeFactory::fromRawSchemeVO(
                    $this->rawSchemeParser->parse($rawSchemeString)
                ));
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