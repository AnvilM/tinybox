<?php

declare(strict_types=1);

namespace App\Commands\Shared\OptionGroup;

use App\Commands\Shared\Interface\OptionGroup\OptionGroupInterface;
use Symfony\Component\Console\Input\InputInterface;

abstract readonly class AbstractOptionGroup implements OptionGroupInterface
{
    protected InputInterface $input;

    public function setInput(InputInterface $input): void
    {
        if (!isset($this->input))
            $this->input = $input;
    }
}