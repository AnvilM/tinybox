<?php

declare(strict_types=1);

namespace App\Commands\Shared\OptionGroup\Groups;

use App\Commands\Shared\OptionGroup\AbstractOptionGroup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

final readonly class BaseOptionsGroup extends AbstractOptionGroup
{
    public function configure(Command $command): void
    {
        $command->addOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug messages');
        $command->addOption('configPath', 'c', InputOption::VALUE_REQUIRED, 'Config path');
        $command->addOption('configOption', 'o', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Specific config option. e.g. -o subscriptions.timeout=10 -o sing_box.outbound_test.max_parallel_requests=20');
    }

    public function getConfigOptions(): ?array
    {
        return $this->input->getOption('configOption') ? $this->parseConfigOptions(
            $this->input->getOption('configOption')
        ) : null;
    }

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

    public function getConfigPath(): ?string
    {
        return $this->input->getOption('configPath');
    }

}