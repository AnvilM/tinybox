<?php

declare(strict_types=1);

namespace App\Infrastructure\OutboundTest\OutboundLatency\Exception;

use App\Domain\Shared\Exception\CoreException;

final class UnableToGetLatencyException extends CoreException
{

}