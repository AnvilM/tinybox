<?php

declare(strict_types=1);

namespace App\Application\RunSingBoxWithConfig\Handler;

use App\Application\RunSingBoxWithConfig\Command\RunSingBoxWithConfigCommand;
use App\Application\RunSingBoxWithConfig\Command\RunSingBoxWithConfigCommandResult;
use App\Application\RunSingBoxWithConfig\Process\RunSingBox;

final readonly class RunSingBoxWithConfigHandler
{
    public function __construct(
        private RunSingBox $runSingBox,
    )
    {
    }

    public function handle(RunSingBoxWithConfigCommand $command): RunSingBoxWithConfigCommandResult
    {
        return new RunSingBoxWithConfigCommandResult(
            $this->runSingBox->run(
                $command->subscriptionName
            )
        );
    }
}