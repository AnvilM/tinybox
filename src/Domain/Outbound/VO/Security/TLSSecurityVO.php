<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO\Security;

use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use Override;

final readonly class TLSSecurityVO extends SecurityVO
{
    public function __construct(
        NonEmptyStringVO          $serverName,
        ?NonEmptyStringVO         $fingerprint,
        private NonEmptyStringVO  $alpn,
        private bool              $insecure = false,
        private ?NonEmptyStringVO $verifyPeerCertByName = null
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

    #[Override]
    public function withServerName(NonEmptyStringVO $serverName): static
    {
        return $this->cloneWith(['serverName' => $serverName]);
    }

    protected function cloneWith(array $changes): static
    {
        return new self(...array_merge([
            'serverName' => $this->getServerName(),
            'fingerprint' => $this->getFingerprint(),
            'alpn' => $this->alpn,
            'insecure' => $this->insecure,
            'verifyPeerCertByName' => $this->verifyPeerCertByName,
        ], $changes));
    }

    #[Override]
    public function withFingerprint(?NonEmptyStringVO $fingerprint): static
    {
        return $this->cloneWith(['fingerprint' => $fingerprint]);
    }

    public function withAlpn(NonEmptyStringVO $alpn): static
    {
        return $this->cloneWith(['alpn' => $alpn]);
    }

    public function getInsecure(): bool
    {
        return $this->insecure;
    }

    public function withInsecure(bool $insecure = false): static
    {
        return $this->cloneWith(['insecure' => $insecure]);
    }

    public function withVerifyPeerCertByName(?NonEmptyStringVO $verifyPeerCertByName): static
    {
        return $this->cloneWith(['verifyPeerCertByName' => $verifyPeerCertByName]);
    }

    public function getVerifyPeerCertByName(): ?NonEmptyStringVO
    {
        return $this->verifyPeerCertByName;
    }


}