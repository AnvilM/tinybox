<?php

declare(strict_types=1);

namespace App\Commands\Shared\Interface\OptionGroup;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * A reusable, self-contained block of CLI options.
 *
 * Deliberately the ONLY contract is "configure yourself on a Command".
 * Resolution (turning InputInterface values back into a domain object) is
 * intentionally NOT part of this interface: different groups resolve into
 * different shapes (an enum, a bag, a scalar...), and forcing one generic
 * `resolve(): mixed` signature on all of them would throw away static
 * typing for no benefit. Instead, callers fetch the concrete group via
 * {@see OptionGroupRegistry::get()} and call its own typed `resolve()`
 * method directly - see the Groups/ folder for examples.
 */
interface OptionGroupInterface
{
    /**
     * Register this group's options on the command.
     *
     * Must be safe to call exactly once per Command instance. Implementations
     * should not hold on to $command or any other mutable state afterwards -
     * a group instance may be registered on multiple commands over the
     * lifetime of the process (e.g. in a long-running worker), and it must
     * stay stateless between `configure()` and `resolve()` calls.
     */
    public function configure(Command $command): void;

    public function setInput(InputInterface $input): void;
}
