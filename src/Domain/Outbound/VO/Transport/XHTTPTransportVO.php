<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO\Transport;

use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final readonly class XHTTPTransportVO extends TransportVO
{


    public function __construct(
        private NonEmptyStringVO $mode,
        private NonEmptyStringVO $host,
        private NonEmptyStringVO $path,
        private array            $extra,
    )
    {
    }

    public function equals(mixed $other): bool
    {
        return parent::equals($other) &&
            $this->path->equals($other->path) &&
            $this->host->equals($other->host) &&
            $this->mode->equals($other->mode) &&
            $this->extra == $other->extra;
    }

    public function getType(): TransportTypeVO
    {
        return TransportTypeVO::XHTTP;
    }

    public function getMode(): NonEmptyStringVO
    {
        return $this->mode;
    }

    public function getHost(): NonEmptyStringVO
    {
        return $this->host;
    }

    public function getPath(): NonEmptyStringVO
    {
        return $this->path;
    }

    public function getExtra(): array
    {
        return $this->extra;
    }


}