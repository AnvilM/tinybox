<?php

declare(strict_types=1);

namespace App\Application\Repository\SchemeGroup;

use App\Application\Repository\Scheme\GetSchemesListRepository;
use App\Application\Repository\SchemeGroup\Shared\File\ReadSchemeGroups;
use App\Application\Repository\SchemeGroup\Shared\File\WriteSchemeGroups;
use App\Application\Repository\SchemeGroup\Shared\SchemeGroupRepository;
use App\Application\Repository\SchemeGroup\Shared\Validator\SchemeGroupsListFormatValidator;
use App\Domain\SchemeGroup\Collection\SchemeGroupMap;

final class SaveSchemeGroupListRepository extends SchemeGroupRepository
{
    public function __construct(ReadSchemeGroups $readSchemeGroups, SchemeGroupsListFormatValidator $schemeGroupsListFormatValidator, GetSchemesListRepository $getSchemesList, WriteSchemeGroups $writeSchemeGroups)
    {
        parent::__construct($readSchemeGroups, $schemeGroupsListFormatValidator, $getSchemesList, $writeSchemeGroups);
    }


    /**
     * @inheritdoc
     */
    public function save(): SchemeGroupMap
    {
        return parent::save();
    }
}