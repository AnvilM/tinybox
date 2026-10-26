<?php

declare(strict_types=1);

namespace App\Application\Shared\UseCase\CreateConfig;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Application\Outbound\Exception\Export\IncompatibleOutboundException;
use App\Application\Outbound\Exception\Export\UnsupportedByCoreException;
use App\Application\Outbound\Export\ExporterRegistryFactory;
use App\Application\Repository\Outbound\GetOutboundsListRepository;
use App\Application\Shared\DTO\UseCase\CreateConfig\ConfigType;
use App\Application\Shared\DTO\UseCase\CreateConfig\CreateConfigDTO;
use App\Application\Shared\UseCase\CreateConfig\FIle\ReadConfigTemplate;
use App\Application\Shared\UseCase\CreateConfig\FIle\ReadOutboundTemplate;
use App\Domain\Outbound\Entity\UrltestOutbound;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use JsonException;

final readonly class CreateConfigUseCase
{

    public function __construct(
        private ReadOutboundTemplate       $readOutboundTemplate,
        private ReadConfigTemplate         $readConfigTemplate,
        private GetOutboundsListRepository $outboundRepository,
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
         * Split the map into regular outbounds and urltest outbounds
         */
        $outbounds = [];
        $urltests = [];

        foreach ($createConfigDTO->outboundsMap->getOutbounds() as $outbound) {
            if ($outbound instanceof UrltestOutbound) {
                $urltests[] = $outbound;
            } else {
                $outbounds[] = $outbound;
            }
        }


        /**
         * Add outbounds to sing-box config template
         */
        foreach ($outbounds as $outbound) {
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
         * Add urltest outbounds
         */
        if ($urltests !== []) {
            match ($createConfigDTO->configType) {
                ConfigType::SingBox => $this->addSingBoxUrltests($configTemplate, $urltests),
                ConfigType::Xray => $this->addXrayUrltests($configTemplate, $urltests),
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
     * Add urltest outbounds to sing-box config
     *
     * NOTE: outbound template is not applied to urltest: its keys are meant for regular outbounds.
     *
     * @param array<string, mixed> $configTemplate
     * @param UrltestOutbound[] $urltests
     */
    private function addSingBoxUrltests(array &$configTemplate, array $urltests): void
    {
        foreach ($urltests as $urltest) {
            try {
                $configTemplate['outbounds'][] = ExporterRegistryFactory::createDefaultExporter()->export($urltest, CoreType::SingBox);
            } catch (IncompatibleOutboundException|UnsupportedByCoreException) {
                continue;
                //TODO: add reporter event
            }
        }
    }

    /**
     * Add one balancer per urltest and one common observatory to xray config
     *
     * @param array<string, mixed> $configTemplate
     * @param UrltestOutbound[] $urltests
     */
    private function addXrayUrltests(array &$configTemplate, array $urltests): void
    {
        /**
         * One balancer per urltest
         */
        $allMembersTags = [];

        foreach ($urltests as $urltest) {
            $membersTags = $urltest->getMembers()->getTagsString()->toArray();
            $allMembersTags = array_merge($allMembersTags, $membersTags);

            $balancer = $urltest->getParameters()->getXrayBalancer();

            $balancer['tag'] = $urltest->getTagString();
            $balancer['selector'] = array_merge($balancer['selector'] ?? [], $membersTags);

            $configTemplate['routing']['balancers'][] = $balancer;
        }


        /**
         * Observatory is common (read once, so it is the same in every urltest):
         * template selectors and members of all urltests
         */
        $observatory = $urltests[0]->getParameters()->getXrayObservatory();

        $observatory['subjectSelector'] = array_values(array_unique(
            array_merge($observatory['subjectSelector'] ?? [], $allMembersTags)
        ));

        $configTemplate['observatory'] = $observatory;
    }
}
