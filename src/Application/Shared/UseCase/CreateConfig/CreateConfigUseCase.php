<?php

declare(strict_types=1);

namespace App\Application\Shared\UseCase\CreateConfig;

use App\Application\Outbound\Exception\Export\IncompatibleOutboundException;
use App\Application\Outbound\Exception\Export\UnsupportedByCoreException;
use App\Application\Outbound\Export\ExporterRegistryFactory;
use App\Application\Shared\DTO\UseCase\CreateConfig\ConfigType;
use App\Application\Shared\DTO\UseCase\CreateConfig\CreateConfigDTO;
use App\Application\Shared\UseCase\CreateConfig\FIle\ReadBalancerTemplate;
use App\Application\Shared\UseCase\CreateConfig\FIle\ReadConfigTemplate;
use App\Application\Shared\UseCase\CreateConfig\FIle\ReadObservatoryTemplate;
use App\Application\Shared\UseCase\CreateConfig\FIle\ReadOutboundTemplate;
use App\Application\Shared\UseCase\CreateConfig\FIle\ReadUrltestTemplate;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use JsonException;

final readonly class CreateConfigUseCase
{

    public function __construct(
        private ReadOutboundTemplate    $readOutboundTemplate,
        private ReadConfigTemplate      $readConfigTemplate,
        private ReadUrltestTemplate     $readUrltestTemplate,
        private ReadObservatoryTemplate $readObservatoryTemplate,
        private ReadBalancerTemplate    $readBalancerTemplate,
    )
    {
    }

    /**
     * Create sing box config
     *
     * @param CreateConfigDTO $createConfigDTO Dto
     *
     * @return string Sing-box config as JSON
     *
     * @throws CriticalException
     */
    public function handle(CreateConfigDTO $createConfigDTO): string
    {
        /**
         * Try to read sing-box config template
         */
        try {
            $configTemplate = $this->readConfigTemplate->read($createConfigDTO->configType);
        } catch (UnableToReadFileException|UnableToDecodeJsonException $e) {
            throw new CriticalException($e instanceof UnableToReadFileException
                ? 'Unable to read config template'
                : 'Config template is invalid'
            );
        }


        /**
         * Try to read outbound template
         */
        try {
            $outboundTemplate = $this->readOutboundTemplate->read($createConfigDTO->configType);
        } catch (UnableToReadFileException|UnableToDecodeJsonException) {
            $outboundTemplate = [];
            //TODO: add reporter event
        }


        /**
         * Add outbounds to sing-box config template
         */
        foreach ($createConfigDTO->outboundsMap->getOutbounds() as $outbound) {
            /**
             * Add outbound to sing-box config outbounds array
             */
            try {
                $configTemplate['outbounds'][] = array_merge($outboundTemplate,
                    ExporterRegistryFactory::createDefaultExporter()->export($outbound, $createConfigDTO->configType->toCoreType())
                );
            } catch (IncompatibleOutboundException|UnsupportedByCoreException) {
                continue;
                //TODO: add reporter event
            }

        }


        /**
         * Add urltest outbound to sing-box config outbounds array
         */

        if ($createConfigDTO->urltestOutbounds && $createConfigDTO->configType === ConfigType::SingBox) {

            /**
             * Try to read urltest outbound template
             */
            try {
                $urltestOutboundTemplate = $this->readUrltestTemplate->read();
            } catch (UnableToReadFileException|UnableToDecodeJsonException) {
                $urltestOutboundTemplate = [];
                //TODO: add reporter event
            }


            $urltestOutboundTemplateOutbounds = $urltestOutboundTemplate['outbounds'] ?? [];
            $urltestOutboundTemplate['outbounds'] = array_merge($urltestOutboundTemplateOutbounds, $createConfigDTO->urltestOutbounds->getTagsString()->toArray());

            $configTemplate['outbounds'][] = $urltestOutboundTemplate;
        }


        /**
         * Add observatory and balancer to xray config
         */

        if ($createConfigDTO->urltestOutbounds && $createConfigDTO->configType === ConfigType::Xray) {
            /**
             * Try to read observatory template
             */
            try {
                $observatoryTemplate = $this->readObservatoryTemplate->read();
            } catch (UnableToReadFileException|UnableToDecodeJsonException) {
                $observatoryTemplate = [];
                //TODO: add reporter event
            }

            $observatoryTemplateOutbounds = $observatoryTemplate['subjectSelector'] ?? [];
            $observatoryTemplate['subjectSelector'] = array_merge($observatoryTemplateOutbounds, $createConfigDTO->urltestOutbounds->getTagsString()->toArray());

            $configTemplate['observatory'] = $observatoryTemplate;


            /**
             * Try to read balancer template
             */
            try {
                $balancerTemplate = $this->readBalancerTemplate->read();
            } catch (UnableToReadFileException|UnableToDecodeJsonException) {
                $balancerTemplate = [];
                //TODO: add reporter event
            }


            $balancerTemplateOutbounds = $balancerTemplate['selector'] ?? [];
            $balancerTemplate['selector'] = array_merge($balancerTemplateOutbounds, $createConfigDTO->urltestOutbounds->getTagsString()->toArray());

            $configTemplate['routing']['balancers'][] = $balancerTemplate;
        }


        /**
         * Try to encode sing-box config array to JSON
         */
        try {
            $singBoxConfig = json_encode($configTemplate,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            throw new CriticalException("Unable to generate sing-box config");
        }

        return $singBoxConfig;
    }
}   