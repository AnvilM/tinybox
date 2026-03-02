<?php

declare(strict_types=1);

namespace App\Application\RunSingBox\Process;

use App\Core\Shared\Ports\Config\ConfigFactoryPort;
use App\Core\Shared\Ports\IO\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\RunSingBox\Process\RunSingBox\RunSingBoxReporterEvent;
use RuntimeException;

/**
 *
 */
final readonly class RunSingBox
{
    public function __construct(
        private ConfigFactoryPort $configFactoryPort,
        private ReporterPort      $reporterPort,
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
        /**
         * Defines descriptors
         */
        $descriptors = [
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR
        ];

        /**
         * Preparing cli command
         */
        $command = "sudo "
            . $this->configFactoryPort->get()->singBoxConfig->binary
            . " run -c "
            . $configPath;

        /**
         * Notify run sing-box
         */
        $this->reporterPort->notify(new RunSingBoxReporterEvent($command));

        /**
         * Opening process
         */
        $singBoxProcess = proc_open(
            $command,
            $descriptors,
            $pipes
        );

        /**
         * Throwing exception on error
         */
        if (is_resource($singBoxProcess)) {
            return proc_close($singBoxProcess);
        }

        throw new RuntimeException("Unable to run sing-box");
    }
}