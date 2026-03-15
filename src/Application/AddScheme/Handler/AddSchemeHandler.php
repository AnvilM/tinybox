<?php

declare(strict_types=1);

namespace App\Application\AddScheme\Handler;

use App\Application\AddScheme\Command\AddSchemeCommand;
use App\Application\Shared\Scheme\Exception\Shared\Parser\UnableToParseRawSchemeStringException;
use App\Application\Shared\Scheme\Shared\File\WriteSchemes;
use App\Application\Shared\Scheme\Shared\Parser\RawSchemeParser;
use App\Application\Shared\Scheme\UseCase\ReadSchemesList\ReadSchemesListUseCase;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use App\Domain\Scheme\Exception\UnsupportedSchemeType;
use App\Domain\Scheme\Factory\SchemeFactory;
use App\Domain\Shared\Exception\CriticalException;
use InvalidArgumentException;

final readonly class AddSchemeHandler
{
    public function __construct(
        private ReadSchemesListUseCase $readSchemesListUseCase,
        private RawSchemeParser        $rawSchemeParser,
        private WriteSchemes           $writeSchemes,
    )
    {
    }

    /**
     * @return string Added scheme id
     *
     * @throws CriticalException
     */
    public function handle(AddSchemeCommand $command): string
    {
        $schemes = $this->readSchemesListUseCase->handle();

        try {
            $newScheme = SchemeFactory::fromRawSchemeVO(
                $this->rawSchemeParser->parse(
                    $command->schemeString
                )
            );
        } catch (UnsupportedSchemeType|UnableToParseRawSchemeStringException|InvalidArgumentException) {
            throw new CriticalException("Invalid scheme provided", $command->schemeString);
        }

        try {
            $schemes->add($newScheme);
        } catch (SchemeAlreadyExistsException) {
            throw new CriticalException("Provided scheme already exists", $command->schemeString);
        }

        $this->writeSchemes->write($schemes);

        return $newScheme->getHash();

    }
}