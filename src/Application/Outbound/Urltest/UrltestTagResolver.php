<?php

declare(strict_types=1);

namespace App\Application\Outbound\Urltest;

use App\Application\Outbound\Exception\Urltest\DuplicateUrltestTagException;
use App\Domain\Outbound\Collection\OutboundMap;
use Psl\Collection\VectorInterface;

/**
 * Urltest tag rules in one place.
 *
 * Tag priority: explicit group tag -> template 'tag' field -> 'urltest'.
 */
final readonly class UrltestTagResolver
{
    private const string DEFAULT_TAG = 'urltest';

    /**
     * @param VectorInterface<UrltestGroupDefinition> $definitions
     * @param string|null $templateTag 'tag' field of the common template, if it is a non-empty string
     * @param OutboundMap $configOutbounds Regular outbounds (to detect collisions)
     *
     * @return list<string> Resulting tags in the order of definitions
     *
     * @throws DuplicateUrltestTagException If a tag is used by more than one urltest or by a regular outbound
     */
    public function resolve(VectorInterface $definitions, ?string $templateTag, OutboundMap $configOutbounds): array
    {
        $outboundTags = array_flip($configOutbounds->getTagsString()->toArray());

        $usedTags = [];
        $tags = [];

        foreach ($definitions as $definition) {
            $tag = $definition->tag ?? $templateTag ?? self::DEFAULT_TAG;

            if (isset($usedTags[$tag])) {
                throw new DuplicateUrltestTagException($tag);
            }

            if (isset($outboundTags[$tag])) {
                throw new DuplicateUrltestTagException($tag, collidesWithOutbound: true);
            }

            $usedTags[$tag] = true;
            $tags[] = $tag;
        }

        return $tags;
    }
}
