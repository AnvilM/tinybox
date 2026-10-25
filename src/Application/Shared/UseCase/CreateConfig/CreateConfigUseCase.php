<?php

declare(strict_types=1);

namespace App\Application\Shared\UseCase\CreateConfig;

use App\Application\Outbound\Exception\Export\IncompatibleOutboundException;
use App\Application\Outbound\Exception\Export\UnsupportedByCoreException;
use App\Application\Outbound\Export\ExporterRegistryFactory;
use App\Application\Repository\Outbound\GetOutboundsListRepository;
use App\Application\Shared\DTO\UseCase\CreateConfig\ConfigType;
use App\Application\Shared\DTO\UseCase\CreateConfig\CreateConfigDTO;
use App\Application\Shared\UseCase\CreateConfig\FIle\ReadBalancerTemplate;
use App\Application\Shared\UseCase\CreateConfig\FIle\ReadConfigTemplate;
use App\Application\Shared\UseCase\CreateConfig\FIle\ReadObservatoryTemplate;
use App\Application\Shared\UseCase\CreateConfig\FIle\ReadOutboundTemplate;
use App\Application\Shared\UseCase\CreateConfig\FIle\ReadUrltestTemplate;
use App\Application\Urltest\Exception\DuplicateUrltestTagException;
use App\Application\Urltest\UrltestTagResolver;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use JsonException;

final readonly class CreateConfigUseCase
{

    public function __construct(
        private ReadOutboundTemplate       $readOutboundTemplate,
        private ReadConfigTemplate         $readConfigTemplate,
        private ReadUrltestTemplate        $readUrltestTemplate,
        private ReadObservatoryTemplate    $readObservatoryTemplate,
        private ReadBalancerTemplate       $readBalancerTemplate,
        private GetOutboundsListRepository $outboundRepository,
        private UrltestTagResolver         $urltestTagResolver,
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
         * Add urltest groups
         */
        if (!$createConfigDTO->urltestGroups->isEmpty()) {
            match ($createConfigDTO->configType) {
                ConfigType::SingBox => $this->addSingBoxUrltests($configTemplate, $createConfigDTO),
                ConfigType::Xray => $this->addXrayUrltests($configTemplate, $createConfigDTO),
            };
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

    /**
     * Add one urltest outbound per group to sing-box config (common template, read once)
     *
     * @throws DuplicateUrltestTagException
     */
    private function addSingBoxUrltests(array &$configTemplate, CreateConfigDTO $createConfigDTO): void
    {
        /**
         * Try to read urltest outbound template
         */
        try {
            $urltestTemplate = $this->readUrltestTemplate->read();
        } catch (UnableToReadFileException|UnableToDecodeJsonException) {
            $urltestTemplate = [];
            //TODO: add reporter event
        }

        $tags = $this->urltestTagResolver->resolve(
            $createConfigDTO->urltestGroups,
            $this->getTemplateTag($urltestTemplate),
            $createConfigDTO->outboundsMap
        );

        foreach ($createConfigDTO->urltestGroups as $index => $group) {
            $urltestOutbound = $urltestTemplate;

            $urltestOutbound['tag'] = $tags[$index];
            $urltestOutbound['outbounds'] = array_merge(
                $urltestTemplate['outbounds'] ?? [],
                $group->outbounds->getTagsString()->toArray()
            );

            $configTemplate['outbounds'][] = $urltestOutbound;
        }
    }

    /**
     * Add one observatory and one balancer per group to xray config (common templates, read once)
     *
     * @throws DuplicateUrltestTagException
     */
    private function addXrayUrltests(array &$configTemplate, CreateConfigDTO $createConfigDTO): void
    {
        /**
         * Try to read observatory template
         */
        try {
            $observatoryTemplate = $this->readObservatoryTemplate->read();
        } catch (UnableToReadFileException|UnableToDecodeJsonException) {
            $observatoryTemplate = [];
            //TODO: add reporter event
        }


        /**
         * Try to read balancer template
         */
        try {
            $balancerTemplate = $this->readBalancerTemplate->read();
        } catch (UnableToReadFileException|UnableToDecodeJsonException) {
            $balancerTemplate = [];
            //TODO: add reporter event
        }

        $tags = $this->urltestTagResolver->resolve(
            $createConfigDTO->urltestGroups,
            $this->getTemplateTag($balancerTemplate),
            $createConfigDTO->outboundsMap
        );


        /**
         * Observatory is common: template selectors and members of all groups
         */
        $allMembersTags = [];

        foreach ($createConfigDTO->urltestGroups as $group) {
            $allMembersTags = array_merge($allMembersTags, $group->outbounds->getTagsString()->toArray());
        }

        $observatoryTemplate['subjectSelector'] = array_values(array_unique(
            array_merge($observatoryTemplate['subjectSelector'] ?? [], $allMembersTags)
        ));

        $configTemplate['observatory'] = $observatoryTemplate;


        /**
         * One balancer per group
         */
        foreach ($createConfigDTO->urltestGroups as $index => $group) {
            $balancer = $balancerTemplate;

            $balancer['tag'] = $tags[$index];
            $balancer['selector'] = array_merge(
                $balancerTemplate['selector'] ?? [],
                $group->outbounds->getTagsString()->toArray()
            );

            $configTemplate['routing']['balancers'][] = $balancer;
        }
    }

    /**
     * Get 'tag' field of the urltest template
     *
     * @return string|null Tag, null if the field is missing or is not a non-empty string
     */
    private function getTemplateTag(array $template): ?string
    {
        $tag = $template['tag'] ?? null;

        return is_string($tag) && $tag !== '' ? $tag : null;
    }
}
