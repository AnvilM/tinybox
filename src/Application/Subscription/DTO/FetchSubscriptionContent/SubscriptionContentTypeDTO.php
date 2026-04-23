<?php

declare(strict_types=1);

namespace App\Application\Subscription\DTO\FetchSubscriptionContent;

enum SubscriptionContentTypeDTO
{
    case CONFIG;

    case SCHEMES;
}
