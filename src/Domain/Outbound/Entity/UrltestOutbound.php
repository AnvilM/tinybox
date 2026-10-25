<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Entity;

use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\VO\ProtocolVO;
use App\Domain\Outbound\VO\Urltest\UrltestParametersVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use Override;

/**
 * Urltest: an outbound that picks the fastest of its members.
 *
 * Members are the same objects as in the main outbounds map (by reference),
 * so everything set on a member (e.g. detour) is visible through the urltest.
 */
final readonly class UrltestOutbound extends Outbound
{
    private OutboundMap $members;
    private UrltestParametersVO $parameters;

    public function __construct(
        string              $tag,
        NonEmptyStringVO    $id,
        OutboundMap         $members,
        UrltestParametersVO $parameters,
    )
    {
        $this->members = $members;
        $this->parameters = $parameters;

        parent::__construct($tag, $id);
    }

    public function getMembers(): OutboundMap
    {
        return $this->members;
    }

    public function getParameters(): UrltestParametersVO
    {
        return $this->parameters;
    }

    #[Override]
    public function getType(): ProtocolVO
    {
        return ProtocolVO::Urltest;
    }

    #[Override]
    public function getServerString(): ?string
    {
        return null;
    }

    #[Override]
    public function getServerPortInt(): ?int
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function equalsContent(mixed $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        $tags = $this->members->getTagsString()->toArray();
        $otherTags = $other->members->getTagsString()->toArray();

        sort($tags);
        sort($otherTags);

        return $tags === $otherTags &&
            $this->parameters->equals($other->parameters);
    }

    protected function cloneWith(array $changes): static
    {
        return new self(...array_merge([
            'tag' => $this->getTagString(),
            'id' => $this->getId(),
            'members' => $this->members,
            'parameters' => $this->parameters,
        ], $changes));
    }
}
