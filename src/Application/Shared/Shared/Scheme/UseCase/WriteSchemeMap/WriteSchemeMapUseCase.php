<?php

declare(strict_types=1);

namespace App\Application\Shared\Scheme\UseCase\AddSchemeMap;

use App\Application\Shared\Common\Scheme\UseCase\ReadSchemesList\ReadSchemesListUseCase;
use App\Domain\Scheme\Collection\SchemeMap;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;

final readonly class AddSchemeMapUseCase
{
    public function __construct(
        private ReadSchemesListUseCase $readSchemesListUseCase,
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
        } catch (UnableToSaveFileException|UnableToEncodeJsonException $e) {
            throw new CriticalException('Unable to save schemes');
        }


        /**
         * Return map of all schemes
         */
        return $schemes;

    }
}