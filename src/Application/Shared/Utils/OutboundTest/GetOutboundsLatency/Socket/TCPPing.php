<?php

declare(strict_types=1);

namespace App\Application\Shared\Utils\OutboundTest\GetOutboundsLatency\Socket;

use App\Application\Shared\Utils\OutboundTest\GetOutboundsLatency\Process\DTO\OutboundFetchResultDTO;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Exception\CriticalException;
use Exception;
use Psl\Async\Exception\CompositeException;
use Psl\Async\TimeoutCancellationToken;
use Psl\DateTime\Duration;
use function Psl\Async\concurrently;
use function Psl\Async\run;
use function Psl\TCP\connect;

final readonly class TCPPing
{
    /**
     * @param OutboundMap $outboundsMap Map of outbounds to fetch ip
     *
     * @return OutboundFetchResultDTO[] Array of outboundFetchResultDTO
     *
     * @throws CriticalException
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

                $result = new OutboundFetchResultDTO((int)(microtime(true) * 1000), $outbound->getTagString());
                run(function () use ($outbound) {
                    $server = $outbound->getServer();
                    $port = $outbound->getServerPort();
                    if ($port === null || $server === null) throw new Exception();

                    $socket = connect($server, $port, cancellation: new TimeoutCancellationToken(Duration::seconds(10)));
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
            throw new CriticalException("Unable to run sockets");
        }


        return $results;
    }
}