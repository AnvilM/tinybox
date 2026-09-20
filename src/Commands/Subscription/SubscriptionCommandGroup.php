<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use Iva\Command\CommandGroup;

/**
 * The `subscription` / `sub` node of the command tree. See
 * App\Commands\Group\GroupCommandGroup's docblock for the general idea —
 * this is the same treatment for the old `subscription:*` namespace:
 *
 *   subscription:create -> subscription create (alias: sub create)
 *   subscription:export -> subscription export (alias: sub export)
 *   subscription:list   -> subscription list   (alias: sub list)
 *   subscription:test   -> subscription test   (alias: sub test)
 *   subscription:update -> subscription update (alias: sub update)
 */
final class SubscriptionCommandGroup extends CommandGroup
{
    protected function configure(): void
    {
        $this->setName('subscription');
        $this->setDescription('Manage subscriptions');
        $this->setAliases(['sub']);
    }
}
