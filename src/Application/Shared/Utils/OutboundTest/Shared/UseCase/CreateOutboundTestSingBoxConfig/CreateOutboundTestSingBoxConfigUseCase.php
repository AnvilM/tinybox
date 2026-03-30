<?php

declare(strict_types=1);

namespace App\Application\Shared\Utils\OutboundTest\Shared\UseCase\CreateOutboundTestSingBoxConfig;


use App\Application\Shared\Utils\OutboundTest\Shared\UseCase\CreateOutboundTestSingBoxConfig\File\ReadOutboundTestOutboundTemplate;
use App\Application\Shared\Utils\OutboundTest\Shared\UseCase\CreateOutboundTestSingBoxConfig\File\ReadOutboundTestSingBoxConfigTemplate;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use JsonException;

final readonly class CreateOutboundTestSingBoxConfigUseCase
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
     * @throws CriticalException
     */
    public function handle(OutboundMap $outboundsMap): string
    {
        /**
         * Try to read outbound test sing-box config template
         */
        try {
            $singBoxConfigTemplate = $this->readOutboundTestSingBoxConfigTemplate->read();
        } catch (UnableToReadFileException|UnableToDecodeJsonException $e) {
            throw new CriticalException($e instanceof UnableToReadFileException
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
            throw new CriticalException("Unable to generate outbound test sing-box config");
        }

        return $singBoxConfig;
    }
}   