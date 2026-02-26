<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\OS;

enum OS
{
    case Linux;
    case Windows;
    case Darwin;
}
