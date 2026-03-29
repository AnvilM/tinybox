<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Config\SingBox\OutboundTest;

use App\Domain\Shared\VO\Config\SingBox\OutboundTest\FetchIp\FetchIpOutboundTestSingBoxConfigVO;
use App\Domain\Shared\VO\Config\SingBox\OutboundTest\Latency\OutboundLatencyTestSingBoxConfigVO;
use App\Domain\Shared\VO\Config\SingBox\OutboundTest\Templates\OutboundTestTemplatesSingBoxConfigVO;

final readonly class OutboundTestSingBoxConfigVO
{
    public function __construct(
        public OutboundTestTemplatesSingBoxConfigVO $templates,
        public string                               $singBoxConfig,
        public FetchIpOutboundTestSingBoxConfigVO   $fetchIp,
        public int                                  $maxParallelRequests,
        public OutboundLatencyTestSingBoxConfigVO   $latency
    )
    {
    }
}