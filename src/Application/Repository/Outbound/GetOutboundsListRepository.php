<?php

declare(strict_types=1);

namespace App\Application\Repository\Outbound;

use App\Application\Repository\Outbound;
use App\Application\Repository\Outbound\Shared\File\ReadOutbounds;
use App\Application\Repository\Outbound\Shared\File\WriteOutbounds;
use App\Application\Repository\Outbound\Shared\Validator\OutboundsListFormatValidator;
use App\Application\Shared\Scheme\CreateSchemeEntityFromString\CreateSchemeEntityFromStringUseCase;
use App\Domain\Outbound\Collection\OutboundMap;

final class GetOutboundsListRepository extends Outbound\Shared\OutboundRepository
{
    public function __construct(ReadOutbounds $readOutbounds, OutboundsListFormatValidator $outboundsListFormatValidator, WriteOutbounds $writeOutbounds, CreateSchemeEntityFromStringUseCase $createSchemeEntityFromStringUseCase)
    {
        parent::__construct($readOutbounds, $outboundsListFormatValidator, $writeOutbounds, $createSchemeEntityFromStringUseCase);
    }

    /**
     * @inheritdoc
     */
    public function getOutboundsList(): OutboundMap
    {
        return parent::getOutboundsList();
    }
}