<?php

declare(strict_types=1);

namespace App\Application\Shared\UseCase\RestartSingBoxService\Process;

use App\Application\Services\Subscription\ApplySubscription\Exception\UnableToRestartSingBoxServiceException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;

final readonly class RestartSingBoxService
{
    public function __construct(
        private ConfigInstancePort $configInstancePort,
    )
    {
    }

    /**
     * Restart systemd sing box service
     *
     * @throws UnableToRestartSingBoxServiceException Throws if unable to restart service
     */
    public function restart(): void
    {

        $escapedServiceName = escapeshellarg($this->configInstancePort->get()->singBoxConfig->systemdServiceName);

        /**
         * Restart service
         */
        $restartCommand = "sudo systemctl restart $escapedServiceName";

        /**
         * Notify restarting service
         */
        // TODO: Add reporter event
        echo "\nrestarting \n";


        /**
         * Execute command
         */
        exec($restartCommand, $output, $resultCode);

        /**
         * Check if unable to restart throw exception
         */
        if ($resultCode !== 0) {
            throw new UnableToRestartSingBoxServiceException(implode("\n", $output));
        }

        /**
         * Notify service restarted successfully
         */
        // TODO: Add reporter event
        echo "\nrestarted \n";
    }
}