<?php

declare(strict_types=1);

namespace App\Application\Services\Subscription\ApplySubscription\CreateSingBoxConfig;

use App\Application\Services\Subscription\ApplySubscription\CreateSingBoxConfig\FIle\ReadOutboundTemplate;
use App\Application\Services\Subscription\ApplySubscription\CreateSingBoxConfig\FIle\ReadSingBoxConfigTemplate;
use App\Application\Services\Subscription\ApplySubscription\CreateSingBoxConfig\FIle\ReadUrltestTemplate;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use JsonException;

final readonly class CreateSingBoxConfigUseCase
{

    public function __construct(
        private ReadOutboundTemplate      $readOutboundTemplate,
        private ReadSingBoxConfigTemplate $readSingBoxConfigTemplate,
        private ReadUrltestTemplate       $readUrltestTemplate,
    )
    {
    }

    /**
     * Create sing box config
     *
     * @param OutboundMap $outboundsMap Outbounds map
     *
     * @return string Sing-box config as JSON
     *
     * @throws CriticalException
     */
    public function handle(OutboundMap $outboundsMap): string
    {
        /**
         * Try to read sing-box config template
         */
        try {
            $singBoxConfigTemplate = $this->readSingBoxConfigTemplate->read();
        } catch (UnableToReadFileException|UnableToDecodeJsonException $e) {
            throw new CriticalException($e instanceof UnableToReadFileException
                ? 'Unable to read sing-box config template'
                : 'Sing-box config template is invalid'
            );
        }


        /**
         * Try to read outbound template
         */
        try {
            $outboundTemplate = $this->readOutboundTemplate->read();
        } catch (UnableToReadFileException|UnableToDecodeJsonException) {
            $outboundTemplate = [];
            //TODO: add reporter event
        }


        /**
         * Try to read urltest outbound template
         */
        try {
            $urltestOutboundTemplate = $this->readUrltestTemplate->read();
        } catch (UnableToReadFileException|UnableToDecodeJsonException) {
            $urltestOutboundTemplate = [];
            //TODO: add reporter event
        }


        /**
         * Add outbounds to sing-box config template
         */
        foreach ($outboundsMap->getOutbounds() as $outbound) {
            /**
             * Add outbound to sing-box config outbounds array
             */
            $singBoxConfigTemplate['outbounds'][] = array_merge($outboundTemplate, $outbound->toArray());


            /**
             * Add outbounds to urltest outbound
             */
            $urltestOutboundTemplate['outbounds'][] = $outbound->getTag();
        }


        /**
         * Add urltest outbound to sing-box config outbounds array
         */
        $singBoxConfigTemplate['outbounds'][] = $urltestOutboundTemplate;


        /**
         * Try to encode sing-box config array to JSON
         */
        try {
            $singBoxConfig = json_encode($singBoxConfigTemplate,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            throw new CriticalException("Unable to generate sing-box config");
        }

        return $singBoxConfig;
    }
}   