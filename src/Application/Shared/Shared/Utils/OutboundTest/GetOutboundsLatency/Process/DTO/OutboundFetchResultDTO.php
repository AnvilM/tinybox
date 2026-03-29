<?php

declare(strict_types=1);

namespace App\Application\Shared\Shared\Utils\OutboundTest\GetOutboundsLatency\Process\DTO;

final class OutboundFetchResultDTO
{
    public bool $isFailed = false;
    private ?int $endTime = null;

    public function __construct(
        private readonly int   $startTime,
        public readonly string $outboundTag
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