<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO\Transport;

use App\Domain\Shared\Trait\ComparesNullable;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final readonly class XHTTPTransportVO extends TransportVO
{
    use ComparesNullable;

    /**
     * "mode", "host" and "path" are optional for XHTTP: when omitted the core
     * falls back to its defaults (mode "auto", path "/", host from server address / SNI)
     */
    public function __construct(
        private ?NonEmptyStringVO $mode = null,
        private ?NonEmptyStringVO $host = null,
        private ?NonEmptyStringVO $path = null,
        private array             $extra = [],
    )
    {
    }

    public function equals(mixed $other): bool
    {
        return parent::equals($other) &&
            $this->equalsNullable($this->path, $other->path) &&
            $this->equalsNullable($this->host, $other->host) &&
            $this->equalsNullable($this->mode, $other->mode) &&
            $this->extra == $other->extra;
    }

    public function getType(): TransportTypeVO
    {
        return TransportTypeVO::XHTTP;
    }

    public function getMode(): ?NonEmptyStringVO
    {
        return $this->mode;
    }

    public function getHost(): ?NonEmptyStringVO
    {
        return $this->host;
    }

    public function getPath(): ?NonEmptyStringVO
    {
        return $this->path;
    }

    public function getExtra(): array
    {
        return $this->extra;
    }


}