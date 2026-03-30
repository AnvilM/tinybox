<?php

declare(strict_types=1);

namespace App\Application\Shared\Shared\Scheme\UseCase\WriteSchemeMap;

use App\Application\Repository\Scheme\Shared\File\WriteSchemes;
use App\Application\Shared\Shared\Shared\Scheme\UseCase\ReadSchemesList\ReadSchemesListUseCase;
use App\Domain\Scheme\Collection\SchemeMap;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;

final readonly class WriteSchemeMapUseCase
{
    public function __construct(
        private ReadSchemesListUseCase $readSchemesListUseCase,
        private WriteSchemes           $writeSchemes,
    )
    {
    }

    /**
     * Write schemes map to file
     *
     * @param SchemeMap $schemeMap Scheme map to save
     *
     * @return SchemeMap Map of all schemes
     *
     * @throws CriticalException
     */
    public function handle(SchemeMap $schemeMap): SchemeMap
    {
        /**
         * Read schemes list
         */
        $schemes = $this->readSchemesListUseCase->handle();


        /**
         * Merge schemes maps
         */
        $schemes->merge($schemeMap);


        /**
         * Try to write schemes to file
         */
        try {
            $this->writeSchemes->write($schemes);
        } catch (UnableToSaveFileException|UnableToEncodeJsonException) {
            throw new CriticalException('Unable to save schemes');
        }


        /**
         * Return map of all schemes
         */
        return $schemes;

    }
}