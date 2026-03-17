<?php

declare(strict_types=1);

namespace App\Application\Shared\Subscription\Exception\UseCase\FetchSchemes;

use App\Domain\Shared\Exception\CoreException;

final class NoValidSchemesFoundException extends CoreException
{

}