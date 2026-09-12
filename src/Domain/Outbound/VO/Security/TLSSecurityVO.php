<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO\Security;

use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final readonly class TLSSecurityVO extends SecurityVO
{
    public function __construct(
        NonEmptyStringVO         $serverName,
        ?NonEmptyStringVO        $fingerprint,
        private NonEmptyStringVO $alpn
    )
    {
        parent::__construct($serverName, $fingerprint);
    }


    public function equals(mixed $other): bool
    {
        return parent::equals($other) &&
            $this->alpn->equals($other->alpn);
    }

    public function getType(): SecurityTypeVO
    {
        return SecurityTypeVO::TLS;
    }

    public function getAlpn(): NonEmptyStringVO
    {
        return $this->alpn;
    }


}