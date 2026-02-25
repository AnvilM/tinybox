<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\UpdateSubscriptionsLifecycle\Mapper\OutboundsMapper;

use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\BreadcrumbsVO;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

final readonly class InvalidOutboundParamsReporterEvent extends ReporterEvent
{
    public function __construct(string $subscriptionName, string $invalidOutboundParamsMessage, string $outboundTag)
    {
        parent::__construct(
            "Invalid outbound params",
            TypeVO::Skipped,
            DebugMessagesVO::create([$invalidOutboundParamsMessage]),
            BreadcrumbsVO::create([$subscriptionName, $outboundTag])
        );
    }
}