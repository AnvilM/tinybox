<?php

declare(strict_types=1);

namespace App\Commands\Shared\Options;

use InvalidArgumentException;
use Iva\Input\Input;
use Iva\Input\Option;
use JsonException;


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
            description: 'Specific config option. e.g. -o subscriptions.timeout=10 -o sing_box.outbound_test.max_parallel_requests=20 -o \'common.dns.endpoints=["https://1.1.1.1/dns-query"]\'',
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
            if (!str_contains($item, '=')) {
                throw new InvalidArgumentException(
                    sprintf('Invalid config option "%s": expected "path=value".', $item)
                );
            }

            [$path, $rawValue] = explode('=', $item, 2);

            $keys = explode('.', $path);
            $value = $this->castConfigValue($rawValue, $item);

            $current = &$result;
            foreach ($keys as $key) {
                // A scalar sitting on the way is overwritten (last option wins).
                if (!is_array($current)) {
                    $current = [];
                }
                $current = &$current[$key];
            }

            $current = $value;
            unset($current);
        }

        return $result;
    }

    private function castConfigValue(string $value, string $item): mixed
    {
        // Inline JSON: arrays / objects
        if ($value !== '' && ($value[0] === '[' || $value[0] === '{')) {
            try {
                return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new InvalidArgumentException(
                    sprintf('Invalid JSON in config option "%s": %s', $item, $e->getMessage()),
                    previous: $e,
                );
            }
        }

        return match (true) {
            $value === 'true' => true,
            $value === 'false' => false,
            $value === 'null' => null,
            is_numeric($value) => str_contains($value, '.') ? (float)$value : (int)$value,
            strlen($value) >= 2 && $value[0] === '"' && $value[-1] === '"' => substr($value, 1, -1),
            default => $value,
        };
    }
}