<?php

declare(strict_types=1);

namespace App\Application\Shared\Utils\OutboundTest\GetIpCountyCode\SingBox\Process;

use App\Application\Shared\Utils\OutboundTest\GetIpCountyCode\SingBox\Process\DTO\OutboundIpDTO;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use Psl\Async\Exception\CompositeException;
use Psl\Collection\MutableVector;
use function Psl\Async\concurrently;
use function Psl\Result\reflect;
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
     * @return MutableVector<OutboundIpDTO> Mutable map of OutboundIpDTO
     *
     * @throws CriticalException
     */
    public function fetchIp(OutboundMap $outboundsMap): MutableVector
    {
        /**
         * Create empty functions array
         */
        $functions = [];


        /**
         * Add to functions sing box calls
         */
        foreach ($outboundsMap->getOutbounds() as $outbound) {
            $functions[] = reflect(fn() => new OutboundIpDTO(
                $outbound->getTag(), execute(
                    $this->configInstancePort->get()->singBoxConfig->binary,
                    [
                        'tools', 'fetch',
                        $this->configInstancePort->get()->singBoxConfig->outboundTest->fetchIp->url,
                        '-c', $this->configInstancePort->get()->singBoxConfig->outboundTest->singBoxConfig,
                        '-o', $outbound->getTag()
                    ]
                )
            ));
        }


        /**
         * Try to run calls concurrency
         */
        try {
            $results = concurrently($functions);
        } catch (CompositeException) {
            throw new CriticalException("Unable to run sing-box");
        }


        /**
         * Create empty OutboundIpsDTO vector
         */
        $ipsVector = new MutableVector([]);


        /**
         * Add succeeded results to vector
         */
        foreach ($results as $result) {
            $result->map(fn(OutboundIpDTO $processResult) => $ipsVector->add($processResult));
        }

        return $ipsVector;
    }
}