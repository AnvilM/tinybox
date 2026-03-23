<?php

declare(strict_types=1);

namespace Application\Bootstrappers;

use App\Commands\Scheme\AddSchemeCommand;
use App\Commands\Scheme\ListSchemesCommand;
use App\Commands\SchemeGroup\AddSchemeToSchemeGroupCommand;
use App\Commands\SchemeGroup\ApplySchemeGroupCommand;
use App\Commands\SchemeGroup\ListSchemeGroupsCommand;
use App\Commands\Subscription\ApplySubscriptionCommand;
use App\Commands\Subscription\CreateSubscriptionCommand;
use App\Commands\Subscription\ListSubscriptionsCommand;
use App\Commands\Subscription\UpdateSubscriptionCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application as CliApp;
use Symfony\Component\Console\Command\Command;


final class CommandsBootstrapper
{
    /** @var array<class-string<Command>> */
    private static array $commands = [
        AddSchemeCommand::class,
        ListSchemesCommand::class,
        AddSchemeToSchemeGroupCommand::class,
        ListSchemeGroupsCommand::class,
        CreateSubscriptionCommand::class,
        ListSubscriptionsCommand::class,
        ApplySubscriptionCommand::class,
        ApplySchemeGroupCommand::class,
        UpdateSubscriptionCommand::class
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