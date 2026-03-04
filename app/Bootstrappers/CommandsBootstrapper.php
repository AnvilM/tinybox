<?php

declare(strict_types=1);

namespace Application\Bootstrappers;

use App\Commands\AddSubscriptionCommand;
use App\Commands\ApplySubscriptionCommand;
use App\Commands\ListConfigsCommand;
use App\Commands\ListSubscriptionsCommand;
use App\Commands\UpdateSubscriptionsCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application as CliApp;
use Symfony\Component\Console\Command\Command;


final class CommandsBootstrapper
{
    /** @var array<class-string<Command>> */
    private static array $commands = [
        UpdateSubscriptionsCommand::class,
        ListSubscriptionsCommand::class,
        ListConfigsCommand::class,
        ApplySubscriptionCommand::class,
        AddSubscriptionCommand::class
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