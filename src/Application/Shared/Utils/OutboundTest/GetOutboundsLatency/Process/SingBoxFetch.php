<?php

declare(strict_types=1);

namespace App\Application\Shared\Utils\OutboundTest\GetOutboundsLatency\Process;

use App\Application\Shared\Utils\OutboundTest\GetOutboundsLatency\Process\DTO\OutboundFetchResultDTO;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use Psl\Async\Exception\CompositeException;
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
     * @return OutboundFetchResultDTO[] Array of outboundFetchResultDTO
     *
     * @throws CriticalException
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

                $result = new OutboundFetchResultDTO((int)(microtime(true) * 1000), $outbound->getTag());


                run(fn() => execute(
                    $this->configInstancePort->get()->singBoxConfig->binary,
                    [
                        'tools', 'fetch',
                        $this->configInstancePort->get()->singBoxConfig->outboundTest->latency->url,
                        '-c', $this->configInstancePort->get()->singBoxConfig->outboundTest->singBoxConfig,
                        '-o', $outbound->getTag()
                    ]
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
            throw new CriticalException("Unable to run sing-box");
        }


        return $results;
    }
}