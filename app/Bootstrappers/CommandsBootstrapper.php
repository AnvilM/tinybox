<?php

declare(strict_types=1);

namespace Application\Bootstrappers;

use App\Commands\Subscription\CreateSubscriptionCommand;
use App\Commands\Subscription\ExportSubscriptionCommand;
use App\Commands\Subscription\ListSubscriptionsCommand;
use App\Commands\Subscription\SubscriptionCommandGroup;
use App\Commands\Subscription\TestSubscriptionCommand;
use App\Commands\Subscription\UpdateSubscriptionCommand;
use Iva\Application;
use Psr\Container\ContainerInterface;


final class CommandsBootstrapper
{

    public static function registerCommands(Application $app, ContainerInterface $container): void
    {
        $app->command($container->get(SubscriptionCommandGroup::class))
            ->subcommand($container->get(CreateSubscriptionCommand::class))->end()
            ->subcommand($container->get(ExportSubscriptionCommand::class))->end()
            ->subcommand($container->get(ListSubscriptionsCommand::class))->end()
            ->subcommand($container->get(TestSubscriptionCommand::class))->end()
            ->subcommand($container->get(UpdateSubscriptionCommand::class));
    }
}