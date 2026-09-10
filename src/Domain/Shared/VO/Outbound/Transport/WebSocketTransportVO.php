<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Outbound\Transport;

use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final readonly class WebSocketTransportVO extends TransportVO
{
    private NonEmptyStringVO $path;
    private NonEmptyStringVO $host;

    public function __construct(NonEmptyStringVO $path, NonEmptyStringVO $host)
    {
        $this->path = $path;
        $this->host = $host;
    }

    public function equals(mixed $other): bool
    {
        return parent::equals($other) &&
            $this->path === $other->path &&
            $this->host === $other->host;
    }

    public function getPath(): NonEmptyStringVO
    {
        return $this->path;
    }

    public function getHost(): NonEmptyStringVO
    {
        return $this->host;
    }


}