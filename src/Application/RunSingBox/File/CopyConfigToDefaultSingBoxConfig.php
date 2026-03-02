<?php

declare(strict_types=1);

namespace App\Application\RunSingBox\File;

use App\Core\Shared\Ports\Config\ConfigFactoryPort;
use RuntimeException;

final readonly class CopyConfigToDefaultSingBoxConfig
{
    public function __construct(
        private ConfigFactoryPort $configFactoryPort

    )
    {
    }

    /**
     * Copy config using cp
     *
     * @param string $configPath Path to generated config file
     *
     * @throws RuntimeException Throws if unable to copy config
     */
    public function copy(string $configPath): void
    {
        $targetPath = $this->configFactoryPort->get()->singBoxConfig->defaultConfigPath;

        $escapedSource = escapeshellarg($configPath);
        $escapedTarget = escapeshellarg($targetPath);

        $command = "sudo cp $escapedSource $escapedTarget";

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new RuntimeException(implode("\n", $output));
        }
    }
}