<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override\Overrides;

use App\Application\Outbound\Override\Override;
use App\Domain\Interface\Outbound\SecurityProvider;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\VO\Security\TLSSecurityVO;

final readonly class OverrideTlsInsecure extends Override
{

    public function __construct(private bool $value)
    {
    }

    public function override(Outbound $outbound): Outbound
    {
        if (!($outbound instanceof SecurityProvider)) return $outbound;

        $security = $outbound->getSecurity();
        if (!($security instanceof TLSSecurityVO)) return $outbound;


        return $outbound->withSecurity(
            $security->withInsecure($this->value)
        );
    }
}