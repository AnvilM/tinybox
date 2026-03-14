<?php

declare(strict_types=1);

namespace App\Application\AddScheme\Handler;

use App\Application\AddScheme\Command\AddSchemeCommand;
use App\Application\Shared\Scheme\Exception\UnableToParseRawSchemeStringException;
use App\Application\Shared\Scheme\Shared\File\WriteSchemes;
use App\Application\Shared\Scheme\Shared\Parser\RawSchemeParser;
use App\Application\Shared\Scheme\UseCase\ReadSchemesList\ReadSchemesListUseCase;
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
     * @throws CriticalException
     */
    public function handle(AddSchemeCommand $command): void
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

        if ($schemes->has($newScheme)) {
            throw new CriticalException("already exists");
        }

        $schemes->add($newScheme);

        $this->writeSchemes->write($schemes);

    }
}