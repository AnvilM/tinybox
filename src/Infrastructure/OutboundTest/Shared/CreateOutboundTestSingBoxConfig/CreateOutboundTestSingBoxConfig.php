<?php

declare(strict_types=1);

namespace App\Infrastructure\OutboundTest\Shared\CreateOutboundTestSingBoxConfig;


use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use App\Infrastructure\OutboundTest\Shared\CreateOutboundTestSingBoxConfig\Exception\CreateOutboundTestSingBoxConfigException;
use App\Infrastructure\OutboundTest\Shared\CreateOutboundTestSingBoxConfig\File\ReadOutboundTestOutboundTemplate;
use App\Infrastructure\OutboundTest\Shared\CreateOutboundTestSingBoxConfig\File\ReadOutboundTestSingBoxConfigTemplate;
use JsonException;

final readonly class CreateOutboundTestSingBoxConfig
{

    public function __construct(
        private ReadOutboundTestOutboundTemplate      $readOutboundTestOutboundTemplate,
        private ReadOutboundTestSingBoxConfigTemplate $readOutboundTestSingBoxConfigTemplate,
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
     * @throws CreateOutboundTestSingBoxConfigException In unable to create outbound test sing-box config
     */
    public function handle(OutboundMap $outboundsMap): string
    {
        /**
         * Try to read outbound test sing-box config template
         */
        try {
            $singBoxConfigTemplate = $this->readOutboundTestSingBoxConfigTemplate->read();
        } catch (UnableToReadFileException|UnableToDecodeJsonException $e) {
            throw new CreateOutboundTestSingBoxConfigException($e instanceof UnableToReadFileException
                ? 'Unable to read outbound test sing-box config template'
                : 'Outbound test sing-box config template is invalid'
            );
        }


        /**
         * Try to read outbound test outbound template
         */
        try {
            $outboundTemplate = $this->readOutboundTestOutboundTemplate->read();
        } catch (UnableToReadFileException|UnableToDecodeJsonException) {
            $outboundTemplate = [];
            //TODO: add reporter event
        }


        /**
         * Add outbounds to outbound test sing-box config template
         */
        foreach ($outboundsMap->getOutbounds() as $outbound) {
            /**
             * Add outbound to outbound test sing-box config outbounds array
             */
            $singBoxConfigTemplate['outbounds'][] = array_merge($outboundTemplate, $outbound->toArray());
        }


        /**
         * Try to encode outbound test sing-box config array to JSON
         */
        try {
            $singBoxConfig = json_encode($singBoxConfigTemplate,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            throw new CreateOutboundTestSingBoxConfigException("Unable to generate outbound test sing-box config");
        }

        return $singBoxConfig;
    }
}   