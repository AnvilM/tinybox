<?php

declare(strict_types=1);

namespace App\Infrastructure\OutboundTest\OutboundLatency;

use App\Application\DTO\Outbound\OutboundLatencyDTO;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\OutboundTest\OutboundLatency\OutboundLatencyPort;
use App\Domain\Shared\VO\Config\SingBox\OutboundTest\Latency\LatencyTestMethod;
use App\Infrastructure\OutboundTest\OutboundLatency\Process\SingBoxFetch;
use App\Infrastructure\OutboundTest\OutboundLatency\Socket\TCPPing;
use App\Infrastructure\OutboundTest\Shared\CreateOutboundTestSingBoxConfig\CreateOutboundTestSingBoxConfig;
use App\Infrastructure\OutboundTest\Shared\CreateOutboundTestSingBoxConfig\File\WriteOutboundTestSingBoxConfig;
use Psl\Collection\MutableVector;

final readonly class OutboundLatency implements OutboundLatencyPort
{
    public function __construct(
        private CreateOutboundTestSingBoxConfig $createOutboundTestSingBoxConfigUseCase,
        private WriteOutboundTestSingBoxConfig  $writeOutboundTestSingBoxConfig,
        private ConfigInstancePort              $configInstancePort,
        private SingBoxFetch                    $singBoxFetch,
        private TCPPing                         $tcpPing,

    )
    {
    }

    /**
     * @inheritdoc
     */
    public function getOutboundsLatency(OutboundMap $outboundsMap, LatencyTestMethod $method): MutableVector
    {
        /**
         * Check if provided outbounds map is not empty
         */
        if ($outboundsMap->isEmpty()) return new MutableVector([]);


        /**
         * Create outbound test sing-box config
         */
        $config = $this->createOutboundTestSingBoxConfigUseCase->handle($outboundsMap);


        /**
         * Try to write outbound test sing-box config
         */
        $this->writeOutboundTestSingBoxConfig->write($config);


        /**
         * Get outbounds map chunked by max sing-box instance count
         */
        $chunkedOutboundsMaps = $outboundsMap->getChunks($this->configInstancePort->get()->singBoxConfig->outboundTest->maxParallelRequests);


        /**
         * Create empty outbounds fetch results DTO mutable map
         */
        $outboundsFetchResults = new MutableVector([]);


        /**
         * Get outbounds fetch results chunked
         */
        foreach ($chunkedOutboundsMaps as $chunkedOutboundsMap) {
            foreach (match ($method) {
                LatencyTestMethod::PROXY_GET => $this->singBoxFetch->fetch($chunkedOutboundsMap),
                LatencyTestMethod::TCP_PING => $this->tcpPing->ping($chunkedOutboundsMap),
            } as $outboundFetchResult) {
                $outboundsFetchResults->add(new OutboundLatencyDTO($outboundFetchResult->outbound, $outboundFetchResult->getDelay()));
            }
        }

        return $outboundsFetchResults;
    }
}