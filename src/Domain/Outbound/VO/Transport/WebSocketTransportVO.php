<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO\Transport;

use App\Domain\Shared\Trait\ComparesNullable;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final readonly class WebSocketTransportVO extends TransportVO
{
    use ComparesNullable;

    private NonEmptyStringVO $path;
    private ?NonEmptyStringVO $host;

    /**
     * @param NonEmptyStringVO|null $host Optional: when omitted, the client falls back to the server address / SNI
     */
    public function __construct(NonEmptyStringVO $path, ?NonEmptyStringVO $host = null)
    {
        $this->path = $path;
        $this->host = $host;
    }

    public function equals(mixed $other): bool
    {
        return parent::equals($other) &&
            $this->path->equals($other->path) &&
            $this->equalsNullable($this->host, $other->host);
    }

    public function getPath(): NonEmptyStringVO
    {
        return $this->path;
    }

    public function getHost(): ?NonEmptyStringVO
    {
        return $this->host;
    }

    public function getType(): TransportTypeVO
    {
        return TransportTypeVO::WebSocket;
    }


}