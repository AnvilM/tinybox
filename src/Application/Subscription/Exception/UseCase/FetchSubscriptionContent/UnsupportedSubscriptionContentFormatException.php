<?php

declare(strict_types=1);

namespace App\Application\Subscription\Exception\UseCase\FetchSubscriptionContent;

use App\Domain\Shared\Exception\CoreException;

final class UnsupportedSubscriptionContentFormatException extends CoreException
{
    public function __construct(public readonly string $rawSubscriptionContent)
    {
        parent::__construct();
    }
}