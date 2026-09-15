<?php

declare(strict_types=1);

namespace App\Commands\Shared\OptionGroup\Groups;

use App\Commands\Shared\OptionGroup\AbstractOptionGroup;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

final readonly class OverridesOptionsGroup extends AbstractOptionGroup
{

    public function configure(Command $command): void
    {
        $command
            ->addOption('override-vless-uuid', null, InputOption::VALUE_REQUIRED, 'Overrides vless uuid')
            ->addOption('override-ss-pass', null, InputOption::VALUE_REQUIRED, 'Overrides shadowsocks password');
    }

    public function getUUID(): ?NonEmptyStringVO
    {
        return $this->input->getOption('override-vless-uuid') === null ? null : new NonEmptyStringVO($this->input->getOption('override-vless-uuid'));
    }

    public function getSSPass(): ?NonEmptyStringVO
    {
        return $this->input->getOption('override-ss-pass') === null ? null : new NonEmptyStringVO($this->input->getOption('override-ss-pass'));
    }
}