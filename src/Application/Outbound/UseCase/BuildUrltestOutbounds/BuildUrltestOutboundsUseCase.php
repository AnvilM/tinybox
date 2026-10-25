<?php

declare(strict_types=1);

namespace App\Application\Outbound\UseCase\BuildUrltestOutbounds;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Application\Outbound\DTO\UseCase\BuildUrltestOutbounds\BuildUrltestOutboundsDTO;
use App\Application\Outbound\DTO\UseCase\SelectOutbounds\SelectOutboundsDTO;
use App\Application\Outbound\Exception\Urltest\DuplicateUrltestTagException;
use App\Application\Outbound\Exception\Urltest\EmptyUrltestGroupException;
use App\Application\Outbound\Selection\Exception\SelectionException;
use App\Application\Outbound\Urltest\Template\ReadBalancerTemplate;
use App\Application\Outbound\Urltest\Template\ReadObservatoryTemplate;
use App\Application\Outbound\Urltest\Template\ReadUrltestTemplate;
use App\Application\Outbound\Urltest\UrltestTagResolver;
use App\Application\Outbound\UseCase\SelectOutbounds\SelectOutboundsUseCase;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Factory\UrltestOutboundFactory;
use App\Domain\Outbound\VO\Urltest\UrltestParametersVO;
use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;

final readonly class BuildUrltestOutboundsUseCase
{
    public function __construct(
        private SelectOutboundsUseCase  $selectOutboundsUseCase,
        private UrltestOutboundFactory  $urltestOutboundFactory,
        private UrltestTagResolver      $urltestTagResolver,
        private ReadUrltestTemplate     $readUrltestTemplate,
        private ReadObservatoryTemplate $readObservatoryTemplate,
        private ReadBalancerTemplate    $readBalancerTemplate,
    )
    {
    }

    /**
     * Create urltest outbounds by definitions
     *
     * NOTE: For sing-box every next group can select the previously created urltests (nesting).
     * For xray the pool does not grow: balancer can not be a member of another balancer.
     *
     * @return OutboundMap Only urltest outbounds, in the order of definitions
     *
     * @throws SelectionException
     * @throws EmptyUrltestGroupException If no outbound got into a group
     * @throws DuplicateUrltestTagException
     */
    public function handle(BuildUrltestOutboundsDTO $dto): OutboundMap
    {
        $result = new OutboundMap();

        if ($dto->definitions->isEmpty()) {
            return $result;
        }


        /**
         * Read templates once (only for the core the config is created for)
         */
        if ($dto->core === CoreType::SingBox) {
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

        $tags = $this->urltestTagResolver->resolve($dto->definitions, $templateTag, $dto->outboundsMap);


        $pool = clone $dto->outboundsMap;

        foreach ($dto->definitions as $index => $definition) {
            $members = $definition->selector === null
                ? clone $pool
                : $this->selectOutboundsUseCase->handle(
                    new SelectOutboundsDTO($pool, selector: $definition->selector)
                );

            if ($members->isEmpty()) {
                throw new EmptyUrltestGroupException($definition->source);
            }

            $urltest = $this->urltestOutboundFactory->create($tags[$index], $members, $parameters);

            $result->add($urltest);

            if ($dto->core === CoreType::SingBox) {
                $pool->add($urltest);
            }
        }

        return $result;
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
