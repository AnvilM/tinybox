<?php

declare(strict_types=1);

namespace App\Application\Outbound\Mapper\ToSchemeString\Shared\Security;

use App\Domain\Outbound\VO\Security\RealitySecurityVO;
use App\Domain\Outbound\VO\Security\SecurityVO;
use InvalidArgumentException;

final readonly class ToSchemeParamsSecurityMapper
{
    /**
     * @throws InvalidArgumentException
     */
    public function map(?SecurityVO $security): array
    {
        if ($security instanceof RealitySecurityVO) {
            return $this->mapRealitySecurity($security);
        }

        if ($security === null) return [];

        throw new InvalidArgumentException();
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
}