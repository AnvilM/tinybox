<?php

declare(strict_types=1);

namespace App\Infrastructure\OutboundTest\OutboundCountyCode;

use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\OutboundTest\OutboundCountyCode\OutboundCountyCodePort;
use App\Infrastructure\OutboundTest\OutboundCountyCode\Geoip\GetIpCountryCode;
use App\Infrastructure\OutboundTest\OutboundCountyCode\SingBox\GetOutboundIp;
use App\Infrastructure\OutboundTest\OutboundCountyCode\SingBox\Process\DTO\OutboundIpDTO;
use App\Infrastructure\OutboundTest\Shared\CreateOutboundTestSingBoxConfig\CreateOutboundTestSingBoxConfig;
use App\Infrastructure\OutboundTest\Shared\CreateOutboundTestSingBoxConfig\File\WriteOutboundTestSingBoxConfig;
use Psl\Collection\MutableMap;
use Psl\Collection\MutableVector;
use UnexpectedValueException;

final readonly class OutboundCountyCode implements OutboundCountyCodePort
{
    public function __construct(
        private GetOutboundIp                   $getOutboundIp,
        private GetIpCountryCode                $getIpCountryCode,
        private CreateOutboundTestSingBoxConfig $createOutboundTestSingBoxConfigUseCase,
        private WriteOutboundTestSingBoxConfig  $writeOutboundTestSingBoxConfig,
        private ConfigInstancePort              $configInstancePort,
    )
    {
    }

    /**
     * @inheritdoc
     */
    public function getCountryCodes(OutboundMap $outboundsMap, bool $outboundIpFallback): MutableMap
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
         * Create empty mutable map of OutboundCountryCodeDTO
         *
         * @var $isoMap MutableMap<string, string>
         */
        $isoMap = new MutableMap([]);


        foreach ($outboundsIps as $outboundIp) {
            /**
             * Try to get iso code for ip
             */
            try {
                $isoMap->add(
                    $outboundIp->outbound->getTagString(), $this->getIpCountryCode->getCountryCode(
                        $outboundIp->getIp() ?? $outboundIpFallback ? (gethostbyname($outboundIp->outbound->getServer()) ?? "") : ""
                ));
            } catch (UnexpectedValueException) {
                continue;
                // TODO: Add reporter event
            }
        }


        return $isoMap;
    }
}