<?php

declare(strict_types=1);

namespace App\Application\Repository\Scheme;

use App\Application\Repository\Scheme\Shared\File\ReadSchemes;
use App\Application\Repository\Scheme\Shared\File\WriteSchemes;
use App\Application\Repository\Scheme\Shared\SchemeRepository;
use App\Application\Repository\Scheme\Shared\Validator\SchemesListFormatValidator;
use App\Application\Shared\Shared\Shared\Scheme\Shared\UseCase\CreateSchemeEntityFromString\CreateSchemeEntityFromStringUseCase;
use App\Domain\Scheme\Collection\SchemeMap;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;

final class GetSchemesList extends SchemeRepository
{
    public function __construct(ReadSchemes $readSchemes, SchemesListFormatValidator $schemesListFormatValidator, CreateSchemeEntityFromStringUseCase $createSchemeEntityFromStringUseCase, ReporterPort $reporterPort, WriteSchemes $writeSchemes)
    {
        parent::__construct($readSchemes, $schemesListFormatValidator, $createSchemeEntityFromStringUseCase, $reporterPort, $writeSchemes);
    }

    /**
     * @inheritdoc
     */
    public function getSchemesList(): SchemeMap
    {
        return parent::getSchemesList();
    }
}