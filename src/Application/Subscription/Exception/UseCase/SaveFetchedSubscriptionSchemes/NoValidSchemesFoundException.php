<?php

declare(strict_types=1);

namespace App\Application\Subscription\Exception\UseCase\SaveFetchedSubscriptionSchemes;

use App\Domain\Shared\Exception\CriticalException;

final class NoValidSchemesFoundException extends CriticalException
{

}