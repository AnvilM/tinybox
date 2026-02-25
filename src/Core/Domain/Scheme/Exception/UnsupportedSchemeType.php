<?php

declare(strict_types=1);

namespace App\Core\Domain\Scheme\Exception;

use RuntimeException;

final class UnsupportedSchemeType extends RuntimeException
{
    public function __construct(string $type)
    {
        parent::__construct("Unsupported scheme type '$type'");
    }
}