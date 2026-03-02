<?php

declare(strict_types=1);

namespace App\Application\RunSingBox\Process;

use App\Core\Shared\Ports\Config\ConfigFactoryPort;
use RuntimeException;

final readonly class RestartSingBoxSystemd
{
    public function __construct(
        private ConfigFactoryPort $configFactoryPort,
    )
    {
    }

    /**
     * Restart systemd sing box service
     *
     * @throws RuntimeException Throws if unable to restart service
     */
    public function reload(): void
    {
        $escapedServiceName = escapeshellarg($this->configFactoryPort->get()->singBoxConfig->systemdServiceName);

        /**
         * Restart service
         */
        $reloadCommand = "sudo systemctl restart $escapedServiceName";

        exec($reloadCommand, $output, $resultCode);

        /**
         * Check if unable to restart throw exception
         */
        if ($resultCode !== 0) {
            throw new RuntimeException(implode("\n", $output));
        }
    }
}