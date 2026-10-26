<?php

declare(strict_types=1);

namespace App\Application\Outbound\Urltest;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Application\Outbound\Exception\Urltest\DuplicateUrltestTagException;
use App\Application\Outbound\Urltest\Template\ReadBalancerTemplate;
use App\Application\Outbound\Urltest\Template\ReadObservatoryTemplate;
use App\Application\Outbound\Urltest\Template\ReadUrltestTemplate;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\VO\Urltest\UrltestParametersVO;
use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use Psl\Collection\Vector;

/**
 * Everything about urltest groups that has to be done once, before the pipeline steps are executed:
 * read the templates and resolve (and check) the tags of all groups.
 *
 * No selection is done here, so the result does not depend on the order of the steps.
 */
final readonly class UrltestPreparer
{
    public function __construct(
        private UrltestTagResolver      $urltestTagResolver,
        private ReadUrltestTemplate     $readUrltestTemplate,
        private ReadObservatoryTemplate $readObservatoryTemplate,
        private ReadBalancerTemplate    $readBalancerTemplate,
    )
    {
    }

    /**
     * @param list<UrltestGroupDefinition> $definitions In the order of flags
     * @param OutboundMap $initialOutbounds Set before the pipeline (to detect tag collisions)
     *
     * @return list<PreparedUrltestGroup> In the order of $definitions
     *
     * @throws DuplicateUrltestTagException
     */
    public function prepare(array $definitions, CoreType $core, OutboundMap $initialOutbounds): array
    {
        if ($definitions === []) {
            return [];
        }


        /**
         * Read templates once (only for the core the config is created for)
         */
        if ($core === CoreType::SingBox) {
            $singBoxTemplate = $this->loadSingBoxTemplate();
            $balancerTemplate = [];
            $observatoryTemplate = [];

            $templateTag = $this->getTemplateTag($singBoxTemplate);
        } else {
            $singBoxTemplate = [];
            $balancerTemplate = $this->loadBalancerTemplate();
            $observatoryTemplate = $this->loadObservatoryTemplate();

            $templateTag = $this->getTemplateTag($balancerTemplate);
        }

        unset($singBoxTemplate['tag'], $balancerTemplate['tag']);

        $parameters = new UrltestParametersVO($singBoxTemplate, $balancerTemplate, $observatoryTemplate);

        $tags = $this->urltestTagResolver->resolve(new Vector($definitions), $templateTag, $initialOutbounds);

        $prepared = [];

        foreach ($definitions as $index => $definition) {
            $prepared[] = new PreparedUrltestGroup($definition, $tags[$index], $parameters);
        }

        return $prepared;
    }

    /**
     * @return array<string, mixed>
     */
    private function loadSingBoxTemplate(): array
    {
        try {
            return $this->readUrltestTemplate->read();
        } catch (UnableToReadFileException|UnableToDecodeJsonException) {
            return [];
            //TODO: add reporter event
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function loadBalancerTemplate(): array
    {
        try {
            return $this->readBalancerTemplate->read();
        } catch (UnableToReadFileException|UnableToDecodeJsonException) {
            return [];
            //TODO: add reporter event
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function loadObservatoryTemplate(): array
    {
        try {
            return $this->readObservatoryTemplate->read();
        } catch (UnableToReadFileException|UnableToDecodeJsonException) {
            return [];
            //TODO: add reporter event
        }
    }

    /**
     * Get 'tag' field of the urltest template
     *
     * @param array<string, mixed> $template
     *
     * @return string|null Tag, null if the field is missing or is not a non-empty string
     */
    private function getTemplateTag(array $template): ?string
    {
        $tag = $template['tag'] ?? null;

        return is_string($tag) && $tag !== '' ? $tag : null;
    }
}
