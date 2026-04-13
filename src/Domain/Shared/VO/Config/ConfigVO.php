<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Config;

use App\Domain\Shared\VO\Config\SingBox\SingBoxConfigVO;
use App\Domain\Shared\VO\Config\Subscriptions\SubscriptionsConfigVO;

final readonly  class ConfigVO
{
    public function __construct(
        public string                $subscriptionsListPath,
        public string                $schemeGroupsListPath,
        public string                $schemesListPath,
        public SubscriptionsConfigVO $subscriptionsConfig,
        public SingBoxConfigVO       $singBoxConfig,
    )
    {
    }
}