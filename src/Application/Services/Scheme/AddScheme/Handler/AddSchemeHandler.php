<?php

declare(strict_types=1);

namespace App\Application\Services\Scheme\AddScheme\Handler;

use App\Application\Exception\Repository\Scheme\UnableToAddSchemeException;
use App\Application\Exception\Repository\Shared\UnableToSaveListException;
use App\Application\Exception\Shared\Scheme\CreateSchemeEntityFromString\UnableToParseRawSchemeStringException;
use App\Application\Repository\Scheme\AddSchemeRepository;
use App\Application\Services\Scheme\AddScheme\Command\AddSchemeCommand;
use App\Application\Shared\Scheme\CreateSchemeEntityFromString\CreateSchemeEntityFromStringUseCase;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use App\Domain\Scheme\Exception\UnsupportedSchemeType;
use App\Domain\Shared\Exception\CriticalException;
use InvalidArgumentException;

final readonly class AddSchemeHandler
{
    public function __construct(
        private CreateSchemeEntityFromStringUseCase $createSchemeEntityFromStringUseCase,
        private AddSchemeRepository                 $addScheme,
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
            $newScheme = $this->createSchemeEntityFromStringUseCase->handle($command->schemeString);
        } catch (UnsupportedSchemeType|UnableToParseRawSchemeStringException|InvalidArgumentException) {
            throw new CriticalException("Invalid scheme provided", $command->schemeString);
        }


        /**
         * Write new scheme
         */
        try {
            $this->addScheme->add($newScheme)->save();
        } catch (UnableToAddSchemeException|SchemeAlreadyExistsException|UnableToSaveListException $e) {
            throw new CriticalException("Unable to add scheme", $e->getMessage());
        }


        /**
         * Return new scheme id
         */
        return $newScheme->getHash();

    }
}