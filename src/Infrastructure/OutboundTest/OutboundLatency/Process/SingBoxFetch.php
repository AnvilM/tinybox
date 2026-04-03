<?php

declare(strict_types=1);

namespace App\Infrastructure\OutboundTest\OutboundLatency\Process;

use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Infrastructure\OutboundTest\OutboundLatency\Exception\UnableToGetLatencyException;
use App\Infrastructure\OutboundTest\OutboundLatency\VO\OutboundFetchResultVO;
use Psl\Async\Exception\CompositeException;
use Psl\Async\TimeoutCancellationToken;
use Psl\DateTime\Duration;
use function Psl\Async\concurrently;
use function Psl\Async\run;
use function Psl\Shell\execute;

final readonly class SingBoxFetch
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
    public function fetch(OutboundMap $outboundsMap): array
    {
        /**
         * Create empty functions array
         */
        $functions = [];


        /**
         * Add to functions sing box calls
         */
        foreach ($outboundsMap->getOutbounds() as $outbound) {
            $functions[] = function () use ($outbound) {

                $result = new OutboundFetchResultVO((int)(microtime(true) * 1000), $outbound);


                run(fn() => execute(
                    $this->configInstancePort->get()->singBoxConfig->binary,
                    [
                        'tools', 'fetch',
                        $this->configInstancePort->get()->singBoxConfig->outboundTest->latency->url,
                        '-c', $this->configInstancePort->get()->singBoxConfig->outboundTest->singBoxConfig,
                        '-o', $outbound->getTagString()
                    ],
                    cancellation: new TimeoutCancellationToken(Duration::seconds(
                        $this->configInstancePort->get()->singBoxConfig->outboundTest->timeout
                    ))
                ))->catch(function () use (&$result) {
                    $result->setFailed();
                })->await();

                $result->setEndTime((int)(microtime(true) * 1000));

                return $result;
            };
        }


        /**
         * Try to run calls concurrency
         */
        try {
            $results = concurrently($functions);
        } catch (CompositeException) {
            throw new UnableToGetLatencyException("Unable to run sing-box");
        }


        return $results;
    }
}