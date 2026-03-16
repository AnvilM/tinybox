<?php

declare(strict_types=1);

namespace Application\Bootstrappers;

use App\Commands\Config\AddSchemeToConfigCommand;
use App\Commands\Config\ListConfigsCommand;
use App\Commands\Scheme\AddSchemeCommand;
use App\Commands\Scheme\ListSchemesCommand;
use App\Commands\Subscription\CreateSubscriptionCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application as CliApp;
use Symfony\Component\Console\Command\Command;


final class CommandsBootstrapper
{
    /** @var array<class-string<Command>> */
    private static array $commands = [
        AddSchemeCommand::class,
        ListSchemesCommand::class,
        AddSchemeToConfigCommand::class,
        ListConfigsCommand::class,
        CreateSubscriptionCommand::class
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