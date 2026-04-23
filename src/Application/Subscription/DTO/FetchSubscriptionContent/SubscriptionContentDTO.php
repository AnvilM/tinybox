<?php

declare(strict_types=1);

namespace App\Application\Subscription\DTO\FetchSubscriptionContent;

final readonly class SubscriptionContentDTO
{
    public function __construct(
        public string                     $content,
        public SubscriptionContentTypeDTO $contentType,
    )
    {
    }
}