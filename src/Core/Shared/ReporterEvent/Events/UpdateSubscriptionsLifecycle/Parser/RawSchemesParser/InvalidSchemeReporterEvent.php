<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\UpdateSubscriptionsLifecycle\Parser\RawSchemesParser;

use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\BreadcrumbsVO;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

final readonly class InvalidSchemeReporterEvent extends ReporterEvent
{
    public function __construct(string $rawSchemesString, string $subscriptionName)
    {
        parent::__construct(
            "Invalid scheme",
            TypeVO::Skipped,
            DebugMessagesVO::create([
                $rawSchemesString
            ]),
            BreadcrumbsVO::create([
                $subscriptionName
            ])
        );
    }
}