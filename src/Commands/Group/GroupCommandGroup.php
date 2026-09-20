<?php

declare(strict_types=1);

namespace App\Commands\Group;

use Iva\Command\CommandGroup;

/**
 * The `group` / `g` node of the command tree. Has no behaviour of its own —
 * running it (or `--help`) just lists its subcommands, exactly like the
 * old `group:*` command namespace did, except the namespace is now a real
 * tree node instead of a colon-separated naming convention:
 *
 *   group:add   -> group add   (alias: g add)
 *   group:apply -> group apply (alias: g apply)
 *   group:list  -> group list  (alias: g list)
 *
 * Wired up once, at application bootstrap:
 *
 *   $app->command(new GroupCommandGroup())
 *       ->subcommand(new AddOutboundToGroupCommand(...))
 *       ->subcommand(new ApplySchemeGroupCommand(...))
 *       ->subcommand(new ListSchemeGroupsCommand(...));
 */
final class GroupCommandGroup extends CommandGroup
{
    protected function configure(): void
    {
        $this->setName('group');
        $this->setDescription('Manage scheme groups');
        $this->setAliases(['g']);
    }
}
