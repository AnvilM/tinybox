<?php

declare(strict_types=1);

namespace App\Commands\Shared\Options;

use App\Application\Shared\DTO\UseCase\CreateConfig\ConfigType;
use Iva\Input\Input;
use Iva\Input\Option;

/**
 * Smallest possible option block: two flags, resolving to an enum.
 * Direct port of CoreOptionsGroup — see BaseOptionsTrait's docblock for why
 * this is a trait rather than an object registered on a registry.
 */
trait CoreOptionsTrait
{
    private Option $singBoxOption;
    private Option $xrayOption;

    protected function configureCoreOptions(): void
    {
        $this->singBoxOption = $this->addOption(Option::flag(
            name: 'sing-box',
            shortcut: 's',
            description: 'Generate config for the sing-box format (used by default)',
        ));

        $this->xrayOption = $this->addOption(Option::flag(
            name: 'xray',
            shortcut: 'x',
            description: 'Generate config for the xray format',
        ));
    }

    protected function resolveConfigType(Input $input): ConfigType
    {
        return match (true) {
            $input->flag($this->xrayOption) => ConfigType::Xray,
            $input->flag($this->singBoxOption) => ConfigType::SingBox,
            default => ConfigType::SingBox,
        };
    }
}
