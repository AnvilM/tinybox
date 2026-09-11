<?php

declare(strict_types=1);

namespace App\Application\Repository\Outbound;

use App\Application\Outbound\Mapper\ToSchemeString\ToSchemeStringOutboundMapper;
use App\Application\Outbound\UseCase\CreateOutboundFromScheme\CreateOutboundFromSchemeUseCase;
use App\Application\Repository\Outbound;
use App\Application\Repository\Outbound\Shared\File\ReadOutbounds;
use App\Application\Repository\Outbound\Shared\File\WriteOutbounds;
use App\Application\Repository\Outbound\Shared\Validator\OutboundsListFormatValidator;
use App\Domain\Outbound\Collection\OutboundMap;

final class GetOutboundsListRepository extends Outbound\Shared\OutboundRepository
{
    public function __construct(ReadOutbounds $readOutbounds, OutboundsListFormatValidator $outboundsListFormatValidator, WriteOutbounds $writeOutbounds, CreateOutboundFromSchemeUseCase $createOutboundFromSchemeUseCase, ToSchemeStringOutboundMapper $toSchemeStringOutboundMapper)
    {
        parent::__construct($readOutbounds, $outboundsListFormatValidator, $writeOutbounds, $createOutboundFromSchemeUseCase, $toSchemeStringOutboundMapper);
    }

    /**
     * @inheritdoc
     */
    public function getOutboundsList(): OutboundMap
    {
        return parent::getOutboundsList();
    }
}