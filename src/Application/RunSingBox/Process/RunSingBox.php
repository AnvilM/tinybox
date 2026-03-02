<?php

declare(strict_types=1);

namespace App\Application\RunSingBox\Process;

use App\Core\Shared\Ports\Config\ConfigFactoryPort;
use RuntimeException;

/**
 *
 */
final readonly class RunSingBox
{
    public function __construct(
        private ConfigFactoryPort $configFactoryPort
    )
    {
    }

    /**
     * Run sing box process with provided config
     *
     * @param string $configPath Path to generated config file
     *
     * @throws RuntimeException Throws if unable to run sing-box process
     */
    public function run(string $configPath): int
    {
        $descriptors = [
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR
        ];

        $command = "sudo "
            . $this->configFactoryPort->get()->singBoxConfig->binary
            . " run -c "
            . $configPath;

        $singBoxProcess = proc_open(
            $command,
            $descriptors,
            $pipes
        );

        if (is_resource($singBoxProcess)) {
            return proc_close($singBoxProcess);
        }

        throw new RuntimeException("Unable to run sing-box");
    }
}