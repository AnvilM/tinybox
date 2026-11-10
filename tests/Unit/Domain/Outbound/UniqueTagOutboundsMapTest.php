<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Outbound;

use App\Domain\Outbound\Collection\UniqueTagOutboundsMap;
use App\Domain\Outbound\Entity\VlessOutbound;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\VO\Security\TLSSecurityVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\PortVO;
use PHPUnit\Framework\TestCase;

final class UniqueTagOutboundsMapTest extends TestCase
{
    private function vless(string $id, string $tag, string $server = '1.1.1.1', ?string $fingerprint = 'chrome'): VlessOutbound
    {
        return new VlessOutbound(
            $tag,
            new NonEmptyStringVO($id),
            new NonEmptyStringVO($server),
            new PortVO(443),
            new NonEmptyStringVO('uuid'),
            null,
            new TLSSecurityVO(
                new NonEmptyStringVO('example.com'),
                $fingerprint === null ? null : new NonEmptyStringVO($fingerprint),
            ),
            null,
        );
    }

    public function testContentDuplicateIgnoresOnlyTheTag(): void
    {
        $map = new UniqueTagOutboundsMap();
        $first = $this->vless('1', 'one');
        $map->add($first);

        $this->assertSame($first, $map->getContentDuplicate($this->vless('2', 'another tag')));
    }

    public function testContentDuplicateIsStrict(): void
    {
        $map = new UniqueTagOutboundsMap();
        $map->add($this->vless('1', 'one'));

        $this->assertNull($map->getContentDuplicate($this->vless('2', 'one', fingerprint: 'firefox')));
        $this->assertNull($map->getContentDuplicate($this->vless('3', 'one', fingerprint: null)));
        $this->assertNull($map->getContentDuplicate($this->vless('4', 'one', server: '2.2.2.2')));
    }

    public function testUniqueTagIsSuffixedOnlyWhenBusy(): void
    {
        $map = new UniqueTagOutboundsMap();

        $this->assertSame('tag', $map->getUniqueTag('tag'));

        $map->add($this->vless('1', 'tag'));
        $this->assertSame('tag #2', $map->getUniqueTag('tag'));

        $map->add($this->vless('2', 'tag #2', server: '2.2.2.2'));
        $this->assertSame('tag #3', $map->getUniqueTag('tag'));
    }

    public function testAddWithUniqueTagKeepsOutboundsWithTheSameTag(): void
    {
        $map = new UniqueTagOutboundsMap();

        $first = $map->addWithUniqueTag($this->vless('1', 'tag', server: '1.1.1.1'));
        $second = $map->addWithUniqueTag($this->vless('2', 'tag', server: '2.2.2.2'));
        $third = $map->addWithUniqueTag($this->vless('3', 'tag', server: '3.3.3.3'));

        $this->assertSame(['tag', 'tag #2', 'tag #3'], [
            $first->getTagString(),
            $second->getTagString(),
            $third->getTagString(),
        ]);
        $this->assertSame(['1', '2', '3'], [
            $first->getIdString(),
            $second->getIdString(),
            $third->getIdString(),
        ]);
        $this->assertSame(3, $map->count());
        $this->assertSame(['tag', 'tag #2', 'tag #3'], $map->getTagsString()->toArray());
    }

    public function testAddWithUniqueTagRejectsExactDuplicate(): void
    {
        $map = new UniqueTagOutboundsMap();
        $map->addWithUniqueTag($this->vless('1', 'tag'));

        $this->expectException(OutboundAlreadyExistsException::class);

        $map->addWithUniqueTag($this->vless('2', 'tag'));
    }
}
