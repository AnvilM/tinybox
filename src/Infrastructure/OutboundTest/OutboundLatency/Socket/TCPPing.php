<?php

declare(strict_types=1);

namespace App\Infrastructure\OutboundTest\OutboundLatency\Socket;

use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Infrastructure\OutboundTest\OutboundLatency\Exception\UnableToGetLatencyException;
use App\Infrastructure\OutboundTest\OutboundLatency\VO\OutboundFetchResultVO;
use Exception;
use Psl\Async\Exception\CompositeException;
use Psl\Async\TimeoutCancellationToken;
use Psl\DateTime\Duration;
use function Psl\Async\concurrently;
use function Psl\Async\run;
use function Psl\TCP\connect;

final readonly class TCPPing
{
    public function __construct(
        private ConfigInstancePort $configInstancePort,
    )
    {
    }

    /**
     * @param OutboundMap $outboundsMap Map of outbounds to fetch ip
     *
     * @return OutboundFetchResultVO[] Array of outboundFetchResultDTO
     *
     * @throws UnableToGetLatencyException If unable to get latency
     */
    public function ping(OutboundMap $outboundsMap): array
    {
        /**
         * Create empty functions array
         */
        $sockets = [];


        /**
         * Add sockets to sockets array
         */
        foreach ($outboundsMap->getOutbounds() as $outbound) {
            $sockets[] = function () use ($outbound) {

                $result = new OutboundFetchResultVO((int)(microtime(true) * 1000), $outbound);
                run(function () use ($outbound) {
                    $server = $outbound->getServer();
                    $port = $outbound->getServerPort();
                    if ($port === null || $server === null) throw new Exception();

                    $socket = connect($server, $port, cancellation: new TimeoutCancellationToken(Duration::seconds(
                        $this->configInstancePort->get()->singBoxConfig->outboundTest->timeout
                    )));
                    $socket->close();

                })->catch(fn() => $result->setFailed())->await();

                $result->setEndTime((int)(microtime(true) * 1000));

                return $result;
            };
        }


        /**
         * Try to run calls concurrency
         */
        try {
            $results = concurrently($sockets);
        } catch (CompositeException) {
            throw new UnableToGetLatencyException("Unable to run sockets");
        }


        return $results;
    }
}