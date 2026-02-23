<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\UpdateSubscriptionsLifecycle\Mapper\RawSchemesMapper;

use App\Core\Scheme\VO\RawSchemeVO;
use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\BreadcrumbsVO;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

final readonly class UnsupportedSchemeReporterEvent extends ReporterEvent
{
    public function __construct(RawSchemeVO $rawSchemeVO, string $subscriptionName, string $unsupportedSchemePropertyMessage)
    {
        parent::__construct(
            "Unsupported scheme",
            TypeVO::Skipped,
            DebugMessagesVO::create([
                $unsupportedSchemePropertyMessage,
                $rawSchemeVO->__toString(),
            ]),
            BreadcrumbsVO::create([
                $subscriptionName,
                $rawSchemeVO->tag
            ])
        );
    }
}