<?php

declare(strict_types=1);

namespace App\Application\RunSingBoxWithConfig\Process;

use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Ports\Config\ConfigFactoryPort;

final readonly class RunSingBox
{
    public function __construct(
        private ConfigFactoryPort $configFactoryPort,
    )
    {
    }

    public function run(string $subscriptionName): int
    {
        echo "running $subscriptionName\n";

        $descriptors = [
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR
        ];

        $command = "sudo "
            . $this->configFactoryPort->get()->singBoxConfig->binary
            . " run -c "
            . $this->configFactoryPort->get()->generatedConfigsDirectoryPath
            . "/$subscriptionName.json";

        $singBoxProcess = proc_open(
            $command,
            $descriptors,
            $pipes
        );

        if (is_resource($singBoxProcess)) {
            return proc_close($singBoxProcess);
        }

        throw new CriticalException("Unable to run sing-box");
    }
}