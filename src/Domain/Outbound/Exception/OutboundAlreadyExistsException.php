<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Exception;

use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Shared\Exception\CoreException;

final class OutboundAlreadyExistsException extends CoreException
{
    public function __construct(public readonly Outbound $outbound)
    {
        parent::__construct();
    }
}