<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override\Overrides;

use App\Application\Outbound\Override\Override;
use App\Application\Outbound\Override\OverrideType;
use App\Domain\Interface\Outbound\SecurityProvider;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\VO\Security\TLSSecurityVO;

final readonly class OverrideVerifyPeerCertByNameFromSni extends Override
{

    public function override(Outbound $outbound, OverrideType $overrideType): Outbound
    {
        if (!($outbound instanceof SecurityProvider)) return $outbound;

        $security = $outbound->getSecurity();
        if (!($security instanceof TLSSecurityVO)) return $outbound;

        return $outbound->withSecurity(
            $security->withVerifyPeerCertByName($security->getServerName())
        );
    }
}