<?php

declare(strict_types=1);

namespace App\Core\Subscription\Exception;

use App\Core\Shared\Exception\OutputException;
use App\Core\Shared\VO\Shared\Output\OutputFMTypeVO;

final class SubscriptionOutputException extends OutputException
{
    public function __construct(string $message, OutputFMTypeVO $type, public string $subscriptionName, ?string $debugMessage = null)
    {
        parent::__construct($message, $type, $debugMessage);
    }
}