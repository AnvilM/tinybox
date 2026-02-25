<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\UpdateSubscriptionsLifecycle\Fetch\FetchSubscriptions;

use App\Core\Domain\Subscription\Entity\Subscription;
use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\BreadcrumbsVO;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

final readonly class InvalidBase64ReporterEvent extends ReporterEvent
{
    public function __construct(Subscription $subscription)
    {
        parent::__construct(
            "Invalid base64 string in response",
            TypeVO::Skipped,
            DebugMessagesVO::create([
                $subscription->url
            ]),
            BreadcrumbsVO::create([
                $subscription->name
            ])
        );
    }
}