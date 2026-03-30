<?php

declare(strict_types=1);

namespace App\Application\Repository\Scheme;

use App\Application\Exception\Repository\Scheme\UnableToAddSchemeException;
use App\Application\Exception\Repository\Scheme\UnableToGetSchemesListException;
use App\Application\Repository\Scheme\Shared\File\ReadSchemes;
use App\Application\Repository\Scheme\Shared\File\WriteSchemes;
use App\Application\Repository\Scheme\Shared\SchemeRepository;
use App\Application\Repository\Scheme\Shared\Validator\SchemesListFormatValidator;
use App\Application\Shared\Shared\Shared\Scheme\Shared\UseCase\CreateSchemeEntityFromString\CreateSchemeEntityFromStringUseCase;
use App\Domain\Scheme\Collection\SchemeMap;
use App\Domain\Scheme\Entity\Scheme;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;

final class AddScheme extends SchemeRepository
{
    public function __construct(ReadSchemes $readSchemes, SchemesListFormatValidator $schemesListFormatValidator, CreateSchemeEntityFromStringUseCase $createSchemeEntityFromStringUseCase, ReporterPort $reporterPort, WriteSchemes $writeSchemes)
    {
        parent::__construct($readSchemes, $schemesListFormatValidator, $createSchemeEntityFromStringUseCase, $reporterPort, $writeSchemes);
    }

    /**
     * Add scheme to repository
     *
     * NOTE: Method doesn't write subscriptions list to file. Use method save
     *
     * @param Scheme $scheme Scheme to save
     *
     * @return self Current AddScheme object
     *
     * @throws UnableToAddSchemeException If unable to add scheme e.g. unable to write file or read
     * @throws SchemeAlreadyExistsException If provided scheme already exists in schemes map
     */
    public function add(Scheme $scheme): self
    {
        /**
         * Try to get all schemes list
         */
        try {
            $schemes = $this->getSchemesList();
        } catch (UnableToGetSchemesListException $e) {
            throw new UnableToAddSchemeException($e->getMessage(), $e->getDebugMessage());
        }


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