<?php

declare(strict_types=1);

namespace App\Domain\Scheme\Exception;

use App\Domain\Shared\Exception\CoreException;

final class UnsupportedSchemeType extends CoreException
{
    public function __construct(public ?string $type)
    {
        parent::__construct("Unsupported scheme type " . "'" . ($this->type ?? 'null') . "'");
    }
}