<?php

declare(strict_types=1);

namespace App\Infrastructure\OutboundTest\OutboundLatency\VO;

use App\Domain\Outbound\Entity\Outbound;

final class OutboundFetchResultVO
{
    public bool $isFailed = false;
    private ?int $endTime = null;

    public function __construct(
        private readonly int     $startTime,
        public readonly Outbound $outbound,
    )
    {
    }

    public function setEndTime(int $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function getDelay(): ?int
    {
        if ($this->isFailed) return null;

        return $this->endTime - $this->startTime;
    }

    public function setFailed(): void
    {
        $this->isFailed = true;
    }
}