<?php

declare(strict_types=1);

namespace App\Application\Outbound\Mapper\ToSchemeString\Shared\Security;

use App\Domain\Outbound\VO\Security\RealitySecurityVO;
use App\Domain\Outbound\VO\Security\SecurityVO;
use App\Domain\Outbound\VO\Security\TLSSecurityVO;
use InvalidArgumentException;

final readonly class ToSchemeParamsSecurityMapper
{
    /**
     * @throws InvalidArgumentException
     */
    public function map(?SecurityVO $security): array
    {
        return match (true) {
            $security instanceof RealitySecurityVO => $this->mapRealitySecurity($security),
            $security instanceof TLSSecurityVO => $this->mapTLSSecurity($security),
            $security === null => [],
            default => throw new InvalidArgumentException("Can't map outbound security to scheme string: Unsupported security type - {$security->getType()->value}")
        };
    }


    private function mapRealitySecurity(RealitySecurityVO $security): array
    {
        $params = [
            'security' => $security->getType()->value,
            'sni' => $security->getServerName()->getValue(),
            'pbk' => $security->getPublicKey()->getValue(),
        ];

        if ($security->getFingerprint()) $params['fp'] = $security->getFingerprint()->getValue();
        if ($security->getShortId()) $params['sid'] = $security->getShortId()->getValue();
        if ($security->getSpiderX()) $params['spx'] = $security->getSpiderX()->getValue();

        return $params;
    }

    private function mapTLSSecurity(TLSSecurityVO $security): array
    {
        $params = [
            'security' => $security->getType()->value,
            'sni' => $security->getServerName()->getValue(),
            'alpn' => $security->getAlpn()->getValue(),
        ];

        if ($security->getFingerprint()) $params['fp'] = $security->getFingerprint()->getValue();

        return $params;
    }
}