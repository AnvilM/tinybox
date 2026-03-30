<?php

declare(strict_types=1);

namespace App\Application\Repository\Scheme;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\Scheme\Shared\File\ReadSchemes;
use App\Application\Repository\Scheme\Shared\File\WriteSchemes;
use App\Application\Repository\Scheme\Shared\SchemeRepository;
use App\Application\Repository\Scheme\Shared\Validator\SchemesListFormatValidator;
use App\Application\Shared\Scheme\CreateSchemeEntityFromString\CreateSchemeEntityFromStringUseCase;
use App\Domain\Scheme\Collection\SchemeMap;
use App\Domain\Scheme\Entity\Scheme;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;

final class AddSchemeRepository extends SchemeRepository
{
    public function __construct(ReadSchemes $readSchemes, SchemesListFormatValidator $schemesListFormatValidator, CreateSchemeEntityFromStringUseCase $createSchemeEntityFromStringUseCase, ReporterPort $reporterPort, WriteSchemes $writeSchemes)
    {
        parent::__construct($readSchemes, $schemesListFormatValidator, $createSchemeEntityFromStringUseCase, $reporterPort, $writeSchemes);
    }

    /**
     * Add scheme to repository
     *
     * NOTE: Method doesn't write schemes list to file. Use method save
     *
     * @param Scheme $scheme Scheme to save
     *
     * @return self Current AddScheme object
     *
     * @throws UnableToGetListException If unable to add scheme e.g. unable to write file or read
     * @throws SchemeAlreadyExistsException If provided scheme already exists in schemes map
     */
    public function add(Scheme $scheme): self
    {
        /**
         * Get all schemes list
         */
        $schemes = $this->getSchemesList();


        /**
         * Add scheme to repository
         */
        $schemes->add($scheme);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function save(): SchemeMap
    {
        return parent::save();
    }
}