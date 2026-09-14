<?php

declare(strict_types=1);

namespace App\Commands\Shared\OptionGroup\Groups;

use App\Application\Shared\DTO\UseCase\CreateConfig\ConfigType;
use App\Commands\Shared\OptionGroup\AbstractOptionGroup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * Smallest possible example of a group: two flags, resolving to an enum.
 * No constructor arguments needed, so a command can just do `new CoreOptionsGroup()`.
 */
final readonly class CoreOptionsGroup extends AbstractOptionGroup
{
    public function configure(Command $command): void
    {
        $command
            ->addOption(
                'sing-box',
                's',
                InputOption::VALUE_NONE,
                'Generate config for the sing-box format (used by default)',
            )
            ->addOption(
                'xray',
                'x',
                InputOption::VALUE_NONE,
                'Generate config for the xray format',
            );
    }

    public function resolve(): ConfigType
    {
        return match (true) {
            (bool)$this->input->getOption('sing-box') => ConfigType::SingBox,
            (bool)$this->input->getOption('xray') => ConfigType::Xray,
            default => ConfigType::SingBox,
        };
    }
}
