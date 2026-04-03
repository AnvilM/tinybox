<?php

declare(strict_types=1);

namespace App\Application\Shared\UseCase\CreateSingBoxConfig;

use App\Application\Shared\UseCase\CreateSingBoxConfig\FIle\ReadOutboundTemplate;
use App\Application\Shared\UseCase\CreateSingBoxConfig\FIle\ReadSingBoxConfigTemplate;
use App\Application\Shared\UseCase\CreateSingBoxConfig\FIle\ReadUrltestTemplate;
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
     * @param null|OutboundMap $urltestOutbounds Outbounds to add to urltest
     *
     * @return string Sing-box config as JSON
     *
     * @throws CriticalException
     */
    public function handle(OutboundMap $outboundsMap, ?OutboundMap $urltestOutbounds = null): string
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
        if ($urltestOutbounds) try {
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

        }


        /**
         * Add urltest outbound to sing-box config outbounds array
         */

        if ($urltestOutbounds) {
            $urltestOutboundTemplateOutbounds = $urltestOutboundTemplate['outbounds'] ?? [];
            $urltestOutboundTemplate['outbounds'] =
                array_merge($urltestOutboundTemplateOutbounds, $urltestOutbounds->getTagsString()->toArray());

            $singBoxConfigTemplate['outbounds'][] = $urltestOutboundTemplate;
        }


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