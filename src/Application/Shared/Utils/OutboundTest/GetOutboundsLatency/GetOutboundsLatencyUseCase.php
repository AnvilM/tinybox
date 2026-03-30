<?php

declare(strict_types=1);

namespace App\Application\Shared\Utils\OutboundTest\GetOutboundsLatency;

use App\Application\Shared\Utils\OutboundTest\GetOutboundsLatency\Process\SingBoxFetch;
use App\Application\Shared\Utils\OutboundTest\GetOutboundsLatency\Socket\TCPPing;
use App\Application\Shared\Utils\OutboundTest\Shared\File\WriteOutboundTestSingBoxConfig;
use App\Application\Shared\Utils\OutboundTest\Shared\UseCase\CreateOutboundTestSingBoxConfig\CreateOutboundTestSingBoxConfigUseCase;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\VO\Config\SingBox\OutboundTest\Latency\LatencyTestMethod;
use Psl\Collection\MutableMap;

final readonly class GetOutboundsLatencyUseCase
{
    public function __construct(
        private CreateOutboundTestSingBoxConfigUseCase $createOutboundTestSingBoxConfigUseCase,
        private WriteOutboundTestSingBoxConfig         $writeOutboundTestSingBoxConfig,
        private ConfigInstancePort                     $configInstancePort,
        private SingBoxFetch                           $singBoxFetch,
        private TCPPing                                $tcpPing,

    )
    {
    }

    /**
     * @param OutboundMap $outboundsMap Map of outbounds to test
     * @param LatencyTestMethod $method Method to test outbounds latency e.g. get via proxy or tcp ping
     *
     * @return MutableMap<string, string> Mutable map of outboundTag => (delay in ms)|null e.g. [Outbound1 => 1000, Outbound2 => null, ...]
     *
     * @throws CriticalException
     *
     */
    public function handle(OutboundMap $outboundsMap, LatencyTestMethod $method): MutableMap
    {
        /**
         * Check if provided outbounds map is not empty
         */
        if ($outboundsMap->isEmpty()) return new MutableMap([]);


        /**
         * Create outbound test sing-box config
         */
        $config = $this->createOutboundTestSingBoxConfigUseCase->handle($outboundsMap);


        /**
         * Try to write outbound test sing-box config
         */
        try {
            $this->writeOutboundTestSingBoxConfig->write($config);
        } catch (UnableToSaveFileException) {
            throw new CriticalException("Unable to write outbound test sing-box config");
        }


        /**
         * Get outbounds map chunked by max sing-box instance count
         */
        $chunkedOutboundsMaps = $outboundsMap->getChunks($this->configInstancePort->get()->singBoxConfig->outboundTest->maxParallelRequests);


        /**
         * Create empty outbounds fetch results DTO mutable map
         */
        $outboundsFetchResults = new MutableMap([]);


        /**
         * Get outbounds fetch results chunked
         */
        foreach ($chunkedOutboundsMaps as $chunkedOutboundsMap) {
            foreach (match ($method) {
                LatencyTestMethod::PROXY_GET => $this->singBoxFetch->fetch($chunkedOutboundsMap),
                LatencyTestMethod::TCP_PING => $this->tcpPing->ping($chunkedOutboundsMap),
            } as $outboundFetchResult) {
                $outboundsFetchResults->add($outboundFetchResult->outboundTag, $outboundFetchResult->getDelay());
            }
        }

        return $outboundsFetchResults;
    }
}