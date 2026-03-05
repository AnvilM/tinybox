<?php

declare(strict_types=1);

namespace App\Application\RunSingBox\Process;

use App\Core\Shared\Ports\Config\ConfigInstancePort;
use App\Core\Shared\Ports\IO\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\RunSingBox\Process\RestartSingBoxSystemd\RestartSingBoxSystemdReporterEvent;
use App\Core\Shared\ReporterEvent\Events\RunSingBox\Process\RestartSingBoxSystemd\SingBoxSystemdSuccessfullyRestarted;
use RuntimeException;

final readonly class RestartSingBoxSystemd
{
    public function __construct(
        private ConfigInstancePort $configInstancePort,
        private ReporterPort       $reporterPort,
    )
    {
    }

    /**
     * Restart systemd sing box service
     *
     * @param string $subscriptionName Subscription/config name to print
     *
     * @throws RuntimeException Throws if unable to restart service
     */
    public function reload(string $subscriptionName): void
    {

        $escapedServiceName = escapeshellarg($this->configInstancePort->get()->singBoxConfig->systemdServiceName);

        /**
         * Restart service
         */
        $restartCommand = "sudo systemctl restart $escapedServiceName";

        /**
         * Notify restarting service
         */
        $this->reporterPort->notify(new RestartSingBoxSystemdReporterEvent(
            $restartCommand, $subscriptionName
        ));


        /**
         * Execute command
         */
        exec($restartCommand, $output, $resultCode);

        /**
         * Check if unable to restart throw exception
         */
        if ($resultCode !== 0) {
            throw new RuntimeException(implode("\n", $output));
        }

        /**
         * Notify service restarted successfully
         */
        $this->reporterPort->notify(new SingBoxSystemdSuccessfullyRestarted($restartCommand, $subscriptionName));
    }
}