<?php

declare(strict_types=1);

namespace App\Infrastructure\OutboundTest\OutboundCountyCode\SingBox\Process;

use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Infrastructure\OutboundTest\OutboundCountyCode\SingBox\Process\DTO\OutboundIpDTO;
use Psl\Async\Exception\CompositeException;
use Psl\Async\TimeoutCancellationToken;
use Psl\Collection\Vector;
use Psl\DateTime\Duration;
use function Psl\Async\concurrently;
use function Psl\Async\run;
use function Psl\Shell\execute;

final readonly class SingBoxFetchIp
{
    public function __construct(
        private ConfigInstancePort $configInstancePort,
    )
    {
    }

    /**
     * @param OutboundMap $outboundsMap Map of outbounds to fetch ip
     *
     * @return Vector<OutboundIpDTO> Vector of OutboundIpDTO
     *
     * @throws CompositeException If unable to get ip
     */
    public function fetchIp(OutboundMap $outboundsMap): Vector
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

                $ip = new OutboundIpDTO($outbound);

                run(fn() => $ip->setIp(
                    execute(
                        $this->configInstancePort->get()->singBoxConfig->binary,
                        [
                            'tools', 'fetch',
                            $this->configInstancePort->get()->singBoxConfig->outboundTest->fetchIp->url,
                            '-c', $this->configInstancePort->get()->singBoxConfig->outboundTest->singBoxConfig,
                            '-o', $outbound->getTagString()
                        ], cancellation: new TimeoutCancellationToken(Duration::seconds(
                        $this->configInstancePort->get()->singBoxConfig->outboundTest->timeout
                    ))
                    )
                ))->catch(function () {
                })->await();

                return $ip;
            };
        }


        /**
         * Run calls concurrency
         */
        return new Vector(concurrently($functions));
    }
}