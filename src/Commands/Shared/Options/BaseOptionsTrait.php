<?php

declare(strict_types=1);

namespace App\Commands\Shared\Options;

use Iva\Input\Input;
use Iva\Input\Option;

/**
 * The block of options every command gets "for free": --debug/-d,
 * --configPath/-c and --configOption/-o. This is the direct port of the
 * previous BaseOptionsGroup.
 *
 * Why a trait and not an object registered on a registry (as the old
 * OptionGroupInterface/OptionGroupRegistry pair did): Iva\Command\Command::
 * addOption() is `protected`, on purpose — only code that runs "as" the
 * Command (the class itself, a subclass, or a trait mixed into it) may
 * register an option. A plain helper object handed the Command instance
 * from outside (the old `configure(Command $command)` contract) simply
 * cannot call it anymore. A trait is the idiomatic way to keep a block of
 * options reusable across unrelated commands while still respecting that:
 * its methods execute in the context of whichever class uses it, exactly
 * as if the code had been copy-pasted into that class.
 *
 * Mixed into AbstractCommand, so no leaf command has to use this trait
 * itself or declare these options again.
 */
trait BaseOptionsTrait
{
    private Option $debugOption;
    private Option $configPathOption;
    private Option $configOptionOption;

    protected function configureBaseOptions(): void
    {
        $this->configPathOption = $this->addOption(Option::string(
            name: 'configPath',
            shortcut: 'c',
            description: 'Config path',
        ));

        $this->configOptionOption = $this->addOption(Option::strings(
            name: 'configOption',
            shortcut: 'o',
            description: 'Specific config option. e.g. -o subscriptions.timeout=10 -o sing_box.outbound_test.max_parallel_requests=20',
        ));
    }


    protected function resolveConfigPath(Input $input): ?string
    {
        return $input->option($this->configPathOption);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function resolveConfigOptions(Input $input): ?array
    {
        $items = $input->option($this->configOptionOption);

        return $items === [] ? null : $this->parseConfigOptions($items);
    }

    /**
     * @param list<string> $items
     * @return array<string, mixed>
     */
    private function parseConfigOptions(array $items): array
    {
        $result = [];

        foreach ($items as $item) {
            [$path, $value] = explode('=', $item, 2);

            $keys = explode('.', $path);

            $value = match (true) {
                $value === 'true' => true,
                $value === 'false' => false,
                $value === 'null' => null,
                is_numeric($value) => str_contains($value, '.') ? (float)$value : (int)$value,
                strlen($value) >= 2 && $value[0] === '"' && $value[-1] === '"' =>
                substr($value, 1, -1),
                default => $value,
            };

            $current = &$result;

            foreach ($keys as $key) {
                $current = &$current[$key];
            }

            $current = $value;

            unset($current);
        }

        return $result;
    }
}
