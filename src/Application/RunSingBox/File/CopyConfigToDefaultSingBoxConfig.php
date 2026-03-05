<?php

declare(strict_types=1);

namespace App\Application\RunSingBox\File;

use App\Application\RunSingBox\Exception\UnableToCopyConfigException;
use App\Core\Shared\Ports\Config\ConfigInstancePort;
use App\Core\Shared\Ports\IO\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\RunSingBox\File\CopyConfigToDefaultSingBoxConfig\CopyConfigReporterEvent;
use RuntimeException;

final readonly class CopyConfigToDefaultSingBoxConfig
{
    public function __construct(
        private ConfigInstancePort $configInstancePort,
        private ReporterPort       $reporterPort
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
        $targetPath = $this->configInstancePort->get()->singBoxConfig->defaultConfigPath;

        $this->reporterPort->notify(
            new CopyConfigReporterEvent($configPath, $targetPath)
        );

        $sourceReal = realpath($configPath);
        $targetReal = file_exists($targetPath) ? realpath($targetPath) : null;

        if ($sourceReal === false) {
            throw new UnableToCopyConfigException("Cannot resolve source path", $configPath);
        }

        if ($targetReal !== null && $sourceReal === $targetReal) return;

        $escapedSource = escapeshellarg($sourceReal);
        $escapedTarget = escapeshellarg($targetPath);

        $command = "sudo cp -f -- $escapedSource $escapedTarget 2>&1";

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new UnableToCopyConfigException("Error", implode("\n", $output));
        }

        if (!is_file($targetPath)) {
            throw new UnableToCopyConfigException("Target file was not created", $targetPath);
        }

        $sourceHash = hash_file('sha256', $sourceReal);
        $targetHash = hash_file('sha256', $targetPath);

        if ($sourceHash === false || $targetHash === false) {
            throw new UnableToCopyConfigException("Failed to calculate file hash");
        }

        if ($sourceHash !== $targetHash) {
            throw new UnableToCopyConfigException("File integrity verification failed (hash mismatch)");
        }
    }
}