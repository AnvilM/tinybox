<?php

declare(strict_types=1);

namespace App\Application\Shared\Scheme\UseCase\AddScheme;

use App\Application\Shared\Common\Scheme\UseCase\ReadSchemesList\ReadSchemesListUseCase;
use App\Application\Shared\Scheme\Shared\File\WriteSchemes;
use App\Domain\Scheme\Collection\SchemeMap;
use App\Domain\Scheme\Entity\Scheme;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;

final readonly class AddSchemeUseCase
{
    public function __construct(
        private WriteSchemes           $writeSchemes,
        private ReadSchemesListUseCase $readSchemesListUseCase
    )
    {
    }


    /**
     * Write once scheme to file
     *
     * @param Scheme $scheme Scheme to save
     *
     * @return SchemeMap Map of all schemes
     *
     * @throws CriticalException
     */
    public function handle(Scheme $scheme): SchemeMap
    {
        /**
         * Read schemes list
         */
        $schemes = $this->readSchemesListUseCase->handle();


        /**
         * Try to add new scheme to schemes list
         */
        try {
            $schemes->add($scheme);
        } catch (SchemeAlreadyExistsException) {
            throw new CriticalException("Provided scheme already exists", $scheme->toRawScheme());
        }


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