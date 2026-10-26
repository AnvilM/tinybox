<?php

declare(strict_types=1);

namespace App\Commands\Shared\Options\Pipeline;

use App\Domain\Shared\Exception\CriticalException;

final class FlagOrderMismatchException extends CriticalException
{
    public function __construct()
    {
        parent::__construct(
            "Unable to determine the order of --override, --urltest and --detour flags. Pass every flag separately, e.g. -u 'a:' -d 'b:'",
        );
    }
}
