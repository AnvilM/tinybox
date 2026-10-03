<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO\Shadowsocks\Userinfo;

use App\Domain\Interface\Shared\Equable;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final readonly class ShadowsocksUserinfoVO implements Equable
{
    private ShadowsocksMethod $method;
    private NonEmptyStringVO $password;


    public function __construct(ShadowsocksMethod $method, NonEmptyStringVO $password)
    {
        $this->method = $method;
        $this->password = $password;
    }

    public function equals(mixed $other): bool
    {
        return $other instanceof static &&
            $other->getMethod() === $this->getMethod() &&
            $other->password->equals($this->password);
    }

    public function getMethod(): ShadowsocksMethod
    {
        return $this->method;
    }

    public function getPassword(): string
    {
        return $this->password->getValue();
    }


}