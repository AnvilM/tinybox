<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Config;

use App\Domain\Shared\VO\Config\SingBox\SingBoxConfigVO;
use App\Domain\Shared\VO\Config\Subscriptions\SubscriptionsConfigVO;
use App\Domain\Shared\VO\Config\Xray\XrayConfigVO;

final readonly  class ConfigVO
{
    public function __construct(
        public string                $subscriptionsListPath,
        public string                $groupsListPath,
        public string                $outboundsListPath,
        public SubscriptionsConfigVO $subscriptionsConfig,
        public SingBoxConfigVO       $singBoxConfig,
        public XrayConfigVO          $xrayConfig,
    )
    {
    }
}