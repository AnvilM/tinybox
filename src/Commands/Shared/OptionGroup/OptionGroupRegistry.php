<?php

declare(strict_types=1);

namespace App\Commands\Shared\OptionGroup;

use App\Commands\Shared\Interface\OptionGroup\OptionGroupInterface;
use App\Commands\Shared\Interface\OptionGroup\OptionGroupInterface as T;
use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Per-command collection of registered {@see OptionGroupInterface} instances.
 *
 * Responsibilities, on purpose, kept to exactly two:
 *   1. call configure() on every group against the owning Command, once;
 *   2. give back the same, already-configured instance by class name so its
 *      caller can call the group's own typed resolve() method.
 *
 * There is no hidden mutable "current DTO" state, no InputInterface stored
 * here either - resolution always takes InputInterface as an explicit
 * argument on the group itself, at the point it's actually needed (inside
 * Command::handle()). That keeps this class trivially safe to use in any
 * order and removes an entire category of "used before initialized" bugs.
 */
final class OptionGroupRegistry
{
    /** @var array<class-string<OptionGroupInterface>, OptionGroupInterface> */
    private array $groups = [];

    public function __construct(private readonly Command $command)
    {
    }

    /**
     * @throws LogicException
     */
    public function register(OptionGroupInterface $group): void
    {
        $class = $group::class;

        if (isset($this->groups[$class])) {
            throw new LogicException(sprintf(
                'Option group "%s" is already registered on command "%s". ' .
                'If you need the same group twice with different settings (e.g. a prefix), ' .
                'give the group\'s resolve() method a parameter instead of registering two instances.',
                $class,
                $this->command->getName() ?? $this->command::class,
            ));
        }

        $group->configure($this->command);
        $this->groups[$class] = $group;
    }

    
    public function setInput(InputInterface $input): void
    {
        foreach ($this->groups as $group) {
            $group->setInput($input);
        }
    }

    /**
     * @template T of OptionGroupInterface
     *
     * @param class-string<T> $groupClass
     *
     * @return T
     * @throws LogicException
     */
    public function get(string $groupClass): OptionGroupInterface
    {
        return $this->groups[$groupClass] ?? throw new LogicException(sprintf(
            'Option group "%s" was not registered on command "%s". ' .
            'Add it to the command\'s optionGroups() method.',
            $groupClass,
            $this->command->getName() ?? $this->command::class,
        ));
    }
}
