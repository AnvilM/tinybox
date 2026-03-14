<?php

declare(strict_types=1);

namespace App\Domain\Shared\Ports\IO\File;

use App\Domain\Shared\Exception\File\UnableToSaveFileException;

interface SaveFileNotifyPort
{
    /**
     * Save content to file
     *
     * @param string $path Path to file
     * @param string $fileContent Content to save
     *
     * @throws UnableToSaveFileException Throws if unable to save file
     */
    public function save(string $path, string $fileContent): void;

    /**
     *
     * Enable notification on start saving file
     *
     * @param string $message Message to print
     */
    public function notifyStartSaving(string $message): self;

    /**
     *
     * Enable notification on saved successfully
     *
     * @param string $message Message to print
     */
    public function notifySavedSuccessfully(string $message): self;

    /**
     *
     * Enable notifications on start saving file and on saved successfully
     *
     * @param string $startMessage Message to print on start
     * @param string $successMessage Message to print on success
     */
    public function notifyStartAndSuccess(string $startMessage, string $successMessage): self;

}