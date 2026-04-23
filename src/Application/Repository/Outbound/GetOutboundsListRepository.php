<?php

declare(strict_types=1);

namespace App\Application\Repository\Outbound;

use App\Application\Repository\Outbound;
use App\Application\Repository\Outbound\Shared\File\ReadOutbounds;
use App\Application\Repository\Outbound\Shared\File\WriteOutbounds;
use App\Application\Repository\Outbound\Shared\Validator\OutboundsListFormatValidator;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Factory\FromRawOutbound\FromRawOutboundFactory;
use App\Domain\Shared\Ports\Outbound\Parser\RawOutboundParserPort;

final class GetOutboundsListRepository extends Outbound\Shared\OutboundRepository
{
    public function __construct(ReadOutbounds $readOutbounds, OutboundsListFormatValidator $outboundsListFormatValidator, WriteOutbounds $writeOutbounds, RawOutboundParserPort $rawOutboundParserPort, FromRawOutboundFactory $fromRawOutboundFactory)
    {
        parent::__construct($readOutbounds, $outboundsListFormatValidator, $writeOutbounds, $rawOutboundParserPort, $fromRawOutboundFactory);
    }

    /**
     * @inheritdoc
     */
    public function getOutboundsList(): OutboundMap
    {
        return parent::getOutboundsList();
    }
}