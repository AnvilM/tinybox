<?php

declare(strict_types=1);

namespace App\Application\Outbound\Mapper\ToSchemeString\Outbound;

use App\Domain\Outbound\Entity\ShadowsocksOutbound;

final readonly class ToSchemeStringShadowsocksOutboundMapper
{
    public function map(ShadowsocksOutbound $outbound): string
    {
        $string = $outbound->getType()->value . '://';
        $string .= base64_encode(
                $outbound->getUserinfo()->getMethod()->value . ':' . $outbound->getUserinfo()->getPassword()
            ) . '@';
        $string .= $outbound->getServer() . ':';
        $string .= $outbound->getServerPort() . '?';

        if ($outbound->getPlugin()) {
            $param = $outbound->getPlugin()->getPlugin()->value;

            if ($outbound->getPlugin()->getPluginOptions()) $param .= ';' . $outbound->getPlugin()->getPluginOptions();

            $string .= 'plugin=' . rawurlencode($param);
        }

        $string .= '#' . rawurlencode($outbound->getTagString());

        return $string;
    }
}