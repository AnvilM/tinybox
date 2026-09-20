<?php

declare(strict_types=1);

namespace App\Commands\Shared\Options;

use Iva\Input\Input;
use Iva\Input\Option;

trait DryRunOptionTrait
{
    private Option $dryRunOption;

    protected function configureDryRunOption(?string $description = null): void
    {
        $this->dryRunOption = $this->addOption(Option::flag(
            name: 'dry-run',
            description: 'Run the command without applying any changes.' . ($description ? (' ' . $description) : '') . ' NOTE: Print result even with -q flag ',
        ));
    }

    protected function isDryRun(Input $input): bool
    {
        return $input->flag($this->dryRunOption);
    }
}