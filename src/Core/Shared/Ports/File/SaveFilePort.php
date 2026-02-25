<?php

declare(strict_types=1);

namespace App\Core\Shared\Ports\File;

use App\Core\Shared\Exception\CriticalException;

interface SaveFilePort
{
    /**
     * Save string to file
     *
     * @param string $path Path to file
     * @param string $fileContent Content to save in file
     * @param string|null $fileTitle File title to print in output
     * @param bool $notifyStartSaving If true notify start saving file
     * @param bool $notifySavingSuccessfully If true notify is saving successfully
     *
     * @return void
     *
     * @throws CriticalException Throws if unable to save file
     */
    public function save(string $path, string $fileContent, ?string $fileTitle = null, bool $notifyStartSaving = false, bool $notifySavingSuccessfully = false): void;
}