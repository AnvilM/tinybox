<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use Iva\Command\CommandGroup;


final class SubscriptionCommandGroup extends CommandGroup
{
    protected function configure(): void
    {
        $this->setName('subscription');
        $this->setDescription('Manage subscriptions');
        $this->setAliases(['sub']);
    }
}
