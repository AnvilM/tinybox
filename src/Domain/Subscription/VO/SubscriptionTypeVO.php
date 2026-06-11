<?php

declare(strict_types=1);

namespace App\Domain\Subscription\VO;

enum SubscriptionTypeVO: string
{
    case Outbounds = 'outbounds';

    case Config = 'config';
}
