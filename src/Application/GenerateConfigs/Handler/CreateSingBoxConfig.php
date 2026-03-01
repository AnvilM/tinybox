<?php

declare(strict_types=1);

namespace App\Application\GenerateConfigs\Handler;

use App\Application\GenerateConfigs\File\ReadOutboundTemplate;
use App\Application\GenerateConfigs\File\ReadSingBoxConfigTemplate;
use App\Application\GenerateConfigs\File\ReadUrltestOutboundTemplate;
use App\Application\GenerateConfigs\File\SaveSingBoxConfig;
use App\Application\GenerateConfigs\Mapper\OutboundsMapper;
use App\Core\Domain\Scheme\Collection\SchemeMap;
use App\Core\Domain\SingBox\Collection\OutboundMap;
use App\Core\Shared\Ports\IO\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\GenerateConfigs\Handler\CreateSingBoxConfig\NoValidOutboundsInSubscriptionFoundReporterEvent;
use App\Core\Shared\ReporterEvent\Events\GenerateConfigs\Handler\CreateSingBoxConfig\UnableToEncodeJSONReporterEvent;
use InvalidArgumentException;
use JsonException;

final readonly class CreateSingBoxConfig
{
    public function __construct(
        private ReadOutboundTemplate        $readOutboundTemplate,
        private ReadUrltestOutboundTemplate $readUrltestTemplate,
        private ReadSingBoxConfigTemplate   $readSingBoxConfigTemplate,
        private ReporterPort                $reporterPort,
        private OutboundsMapper             $outboundsMapper,
        private SaveSingBoxConfig           $saveSingBoxConfig,
    )
    {
    }

    public function create(SchemeMap $schemeMap): void
    {
        $outboundTemplate = $this->readOutboundTemplate->read();
        $urltestOutboundTemplate = $this->readUrltestTemplate->read();
        $singBoxConfigTemplate = $this->readSingBoxConfigTemplate->read();

        $outboundMap = $this->mapSchemeMapToOutboundMap($schemeMap);

        foreach ($outboundMap as $subscriptionName => $outboundCollection) {
            $singBoxConfig = $singBoxConfigTemplate;
            $urltestOutbound = $urltestOutboundTemplate;
            $mergedOutboundArray = [];
            foreach ($outboundCollection as $outbound) {
                $mergedOutboundArray[] = array_merge($outboundTemplate, $outbound->toArray());
                $urltestOutbound["outbounds"][] = $outbound->getTag();
            }
            $mergedOutboundArray[] = $urltestOutbound;

            $singBoxConfig['outbounds'] = $mergedOutboundArray;
            try {
                $this->saveSingBoxConfig->save(json_encode($singBoxConfig, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), $subscriptionName);
            } catch (JsonException) {
                $this->reporterPort->notify(new UnableToEncodeJSONReporterEvent($subscriptionName));
                continue;
            }

        }
    }

    /**
     * Maps SchemeMap e.g., ["subscriptionName" => schemeCollection...] to OutboundMap e.g., ["subscriptionName" => OutboundCollection...]
     *
     * @param SchemeMap $schemeMap SchemeMap ["subscriptionName" => schemeCollection...]
     *
     * @return OutboundMap OutboundMap ["subscriptionName" => OutboundCollection...]
     */
    private function mapSchemeMapToOutboundMap(SchemeMap $schemeMap): OutboundMap
    {
        $outboundMap = new OutboundMap();

        foreach ($schemeMap as $subscriptionName => $scheme) {
            try {
                $outboundMap[$subscriptionName] = $this->outboundsMapper->map(
                    $scheme, $subscriptionName
                );
            } catch (InvalidArgumentException) {
                $this->reporterPort->notify(new NoValidOutboundsInSubscriptionFoundReporterEvent(
                    $subscriptionName,
                ));
            }
        }

        return $outboundMap;
    }
}