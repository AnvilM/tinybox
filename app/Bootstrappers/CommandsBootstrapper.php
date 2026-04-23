<?php

declare(strict_types=1);

namespace Application\Bootstrappers;

use App\Commands\Group\AddOutboundToGroupCommand;
use App\Commands\Group\ApplySchemeGroupCommand;
use App\Commands\Group\ListSchemeGroupsCommand;
use App\Commands\Scheme\AddSchemeCommand;
use App\Commands\Scheme\ListSchemesCommand;
use App\Commands\Scheme\ToOutboundsCommand;
use App\Commands\Subscription\ApplySubscriptionCommand;
use App\Commands\Subscription\CreateSubscriptionCommand;
use App\Commands\Subscription\ListSubscriptionsCommand;
use App\Commands\Subscription\TestSubscriptionCommand;
use App\Commands\Subscription\UpdateSubscriptionCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application as CliApp;
use Symfony\Component\Console\Command\Command;


final class CommandsBootstrapper
{
    /** @var array<class-string<Command>> */
    private static array $commands = [
        AddOutboundToGroupCommand::class,
        ListSchemeGroupsCommand::class,
        CreateSubscriptionCommand::class,
        ListSubscriptionsCommand::class,
        ApplySubscriptionCommand::class,
        ApplySchemeGroupCommand::class,
        UpdateSubscriptionCommand::class,
        TestSubscriptionCommand::class,
    ];


    public static function registerCommands(CliApp $app, ContainerInterface $container): void
    {

        foreach (self::$commands as $commandClass) {
            /** @var Command $command */
            $command = $container->get($commandClass);

            $app->addCommand($command);
        }
    }
}