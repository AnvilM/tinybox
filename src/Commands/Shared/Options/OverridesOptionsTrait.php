<?php

declare(strict_types=1);

namespace App\Commands\Shared\Options;

use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use Iva\Input\Input;
use Iva\Input\Option;

/**
 * Direct port of OverridesOptionsGroup — see BaseOptionsTrait's docblock
 * for why this is a trait rather than an object registered on a registry.
 */
trait OverridesOptionsTrait
{
    private Option $overrideVlessUuidOption;
    private Option $overrideSsPassOption;

    protected function configureOverridesOptions(): void
    {
        $this->overrideVlessUuidOption = $this->addOption(Option::string(
            name: 'override-vless-uuid',
            description: 'Overrides vless uuid',
        ));

        $this->overrideSsPassOption = $this->addOption(Option::string(
            name: 'override-ss-pass',
            description: 'Overrides shadowsocks password',
        ));
    }

    protected function resolveOverrideUUID(Input $input): ?NonEmptyStringVO
    {
        $value = $input->option($this->overrideVlessUuidOption);

        return $value === null ? null : new NonEmptyStringVO($value);
    }

    protected function resolveOverrideSSPass(Input $input): ?NonEmptyStringVO
    {
        $value = $input->option($this->overrideSsPassOption);

        return $value === null ? null : new NonEmptyStringVO($value);
    }
}
