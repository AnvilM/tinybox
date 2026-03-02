<?php

declare(strict_types=1);

namespace App\Application\ScanGeneratedConfigsDirectory\Handler;

use App\Application\ScanGeneratedConfigsDirectory\Command\ScanGeneratedConfigsDirectoryCommandResult;
use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Ports\Config\ConfigFactoryPort;
use App\Core\Shared\Ports\IO\Directory\ScanDirectoryForFilesPort;
use App\Core\Shared\Ports\IO\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\ScanGeneratedConfigsDirectory\Handler\ScanGeneratedConfigsDirectoryHandler\SearchingConfigFilesReporterEvent;
use InvalidArgumentException;
use RuntimeException;

final readonly class ScanGeneratedConfigsDirectoryHandler
{
    public function __construct(
        private ScanDirectoryForFilesPort $scanDirectoryForFilesPort,
        private ConfigFactoryPort         $configFactoryPort,
        private ReporterPort              $reporterPort,
    )
    {
    }

    /**
     * Scan generated configs directory and return filenames from directory
     *
     * @return ScanGeneratedConfigsDirectoryCommandResult
     *
     * @throws CriticalException Throws if unable to read generated configs directory or is not a valid directory
     */
    public function handle(): ScanGeneratedConfigsDirectoryCommandResult
    {
        /**
         * Get generated config directory path from app config
         */
        $generatedConfigsDirectoryPath = $this->configFactoryPort->get()->generatedConfigsDirectoryPath;

        /**
         * Notify scanning directory
         */
        $this->reporterPort->notify(new SearchingConfigFilesReporterEvent(
            $generatedConfigsDirectoryPath
        ));

        /**
         * Trying to scan directory for files
         */
        try {

            $configFiles = $this->scanDirectoryForFilesPort->scan($generatedConfigsDirectoryPath);

        } catch (InvalidArgumentException|RuntimeException $e) {

            /**
             * Throw critical exception on unable to scan directory
             */
            throw new CriticalException(($e instanceof InvalidArgumentException)
                ? "Not a valid directory provided"
                : "Unable to read directory",
                $generatedConfigsDirectoryPath
            );
        }

        $result = [];

        /**
         * Removing extensions from files
         */
        foreach ($configFiles as $configFile) {
            $extension = pathinfo($configFile, PATHINFO_EXTENSION);

            if ($extension === '') {
                $result[] = $configFile;
                continue;
            }

            $result[] = substr($configFile, 0, -(strlen($extension) + 1));
        }

        return new ScanGeneratedConfigsDirectoryCommandResult(
            $result
        );
    }
}