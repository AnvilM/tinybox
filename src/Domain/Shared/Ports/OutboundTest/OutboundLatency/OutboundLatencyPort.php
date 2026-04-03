<?php

declare(strict_types=1);

namespace App\Domain\Shared\Ports\OutboundTest\OutboundLatency;

use App\Application\DTO\Outbound\OutboundLatencyDTO;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\VO\Config\SingBox\OutboundTest\Latency\LatencyTestMethod;
use App\Infrastructure\OutboundTest\OutboundLatency\Exception\UnableToGetLatencyException;
use App\Infrastructure\OutboundTest\Shared\CreateOutboundTestSingBoxConfig\Exception\CreateOutboundTestSingBoxConfigException;
use Psl\Collection\MutableVector;

interface OutboundLatencyPort
{
    /**
     * Get outbounds latency
     *
     * @param OutboundMap $outboundsMap Map of outbounds to test
     * @param LatencyTestMethod $method Method to test outbounds latency e.g. get via proxy or tcp ping
     *
     * @return MutableVector<OutboundLatencyDTO> Mutable vector of OutboundLatencyDTO
     *
     * @throws CreateOutboundTestSingBoxConfigException If unable to create outbound test sing-box config
     * @throws UnableToSaveFileException If unable to save sing box outbound test config
     * @throws UnableToGetLatencyException If unable to get latency
     */
    public function getOutboundsLatency(OutboundMap $outboundsMap, LatencyTestMethod $method): MutableVector;
}