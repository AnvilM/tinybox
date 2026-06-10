<?php

declare(strict_types=1);

namespace App\Domain\Subscription\VO;

final readonly class RawOutboundsSubscriptionVO extends RawSubscriptionVO
{
    public function __construct(
        string       $name,
        string       $url,
        string       $type,
        public array $outbounds)
    {
        parent::__construct($name, $url, $type);
    }
}