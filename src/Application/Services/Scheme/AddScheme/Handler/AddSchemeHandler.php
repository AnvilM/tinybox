<?php

declare(strict_types=1);

namespace App\Application\Services\Scheme\AddScheme\Handler;

use App\Application\Services\Scheme\AddScheme\Command\AddSchemeCommand;
use App\Application\Shared\Scheme\Exception\Shared\Parser\UnableToParseRawSchemeStringException;
use App\Application\Shared\Shared\Scheme\UseCase\WriteScheme\WriteSchemeUseCase;
use App\Application\Shared\Shared\Shared\Scheme\Parser\RawSchemeParser;
use App\Application\Shared\Shared\Shared\Scheme\UseCase\ReadSchemesList\ReadSchemesListUseCase;
use App\Domain\Scheme\Exception\UnsupportedSchemeType;
use App\Domain\Scheme\Factory\SchemeFactory;
use App\Domain\Shared\Exception\CriticalException;
use InvalidArgumentException;

final readonly class AddSchemeHandler
{
    public function __construct(
        private ReadSchemesListUseCase $readSchemesListUseCase,
        private RawSchemeParser        $rawSchemeParser,
        private WriteSchemeUseCase     $writeSchemeUseCase,
    )
    {
    }

    /**
     * Add new scheme to schemes list
     *
     * @return string Added scheme id
     *
     * @throws CriticalException
     */
    public function handle(AddSchemeCommand $command): string
    {
        /**
         * Try to create new scheme
         */
        try {
            $newScheme = SchemeFactory::fromRawSchemeVO(
                $this->rawSchemeParser->parse(
                    $command->schemeString
                )
            );
        } catch (UnsupportedSchemeType|UnableToParseRawSchemeStringException|InvalidArgumentException) {
            throw new CriticalException("Invalid scheme provided", $command->schemeString);
        }


        /**
         * Write new scheme
         */
        $this->writeSchemeUseCase->handle($newScheme);


        /**
         * Return new scheme id
         */
        return $newScheme->getHash();

    }
}