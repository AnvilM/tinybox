<?php

declare(strict_types=1);

namespace App\Application\Shared\Utils\OutboundTest\GetIpCountyCode;

use App\Application\Shared\Utils\OutboundTest\GetIpCountyCode\SingBox\GetOutboundIp;
use App\Application\Shared\Utils\OutboundTest\GetIpCountyCode\SingBox\Process\DTO\OutboundIpDTO;
use App\Application\Shared\Utils\OutboundTest\Shared\File\WriteOutboundTestSingBoxConfig;
use App\Application\Shared\Utils\OutboundTest\Shared\UseCase\CreateOutboundTestSingBoxConfig\CreateOutboundTestSingBoxConfigUseCase;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\Geoip\GetIpCountryCodePort;
use Psl\Collection\MutableMap;
use Psl\Collection\MutableVector;
use UnexpectedValueException;

final readonly class GetIpCountryCodesMapUseCase
{
    public function __construct(
        private GetOutboundIp                          $getOutboundIp,
        private GetIpCountryCodePort                   $getIpCountryCodePort,
        private CreateOutboundTestSingBoxConfigUseCase $createOutboundTestSingBoxConfigUseCase,
        private WriteOutboundTestSingBoxConfig         $writeOutboundTestSingBoxConfig,
        private ConfigInstancePort                     $configInstancePort,
    )
    {
    }

    /**
     * @param OutboundMap $outboundsMap Map of outbounds to fetch ip
     * @param bool $outboundIpFallback Use default outbound ip if unable to get real ip
     *
     * @return MutableMap<Outbound, string> Mutable map of outboundTag => IsoCode e.g. [Outbound1 => US, Outbound2 => UK, ...]
     *
     * @throws CriticalException
     *
     */
    public function getCountryCodesMap(OutboundMap $outboundsMap, bool $outboundIpFallback): MutableMap
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
         * Get outbounds map checked by max sing-box instance count
         */
        $chunkedOutboundsMaps = $outboundsMap->getChunks($this->configInstancePort->get()->singBoxConfig->outboundTest->maxParallelRequests);


        /**
         * Create empty outbounds ips DTO vector
         *
         * @var MutableVector<OutboundIpDTO> $outboundsIps
         */
        $outboundsIps = new MutableVector([]);


        /**
         * Get outbounds ips chunked
         */
        foreach ($chunkedOutboundsMaps as $chunkedOutboundsMap) {
            foreach ($this->getOutboundIp->getOutboundIp($chunkedOutboundsMap) as $outboundIp) {
                $outboundsIps->add($outboundIp);
            }
        }


        /**
         * Create empty mutable map of outboundTag => IsoCode
         *
         * @var $isoMap MutableMap<string, string>
         */
        $isoMap = new MutableMap([]);


        foreach ($outboundsIps as $outboundIp) {
            /**
             * Try to get iso code for ip
             */
            try {

                $isoMap->add($outboundIp->outboundTag, $this->getIpCountryCodePort->getCountryCode(
                        $outboundIp->getIp() ?? $outboundIpFallback ? $outboundIp->outboundIp : ""
                ));
            } catch (UnexpectedValueException) {
                continue;
                // TODO: Add reporter event
            }
        }

        return $isoMap;
    }
}