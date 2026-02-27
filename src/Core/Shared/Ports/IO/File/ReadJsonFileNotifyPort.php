<?php

declare(strict_types=1);

namespace App\Core\Shared\Ports\IO\File;

use App\Core\Shared\Exception\File\UnableToDecodeJSONException;
use App\Core\Shared\Exception\File\UnableToReadFileException;

interface ReadJsonFileNotifyPort
{
    /**
     * Read and parse json file
     *
     * @param string $path Path to file
     *
     * @return array Json decoded array
     *
     * @throws UnableToReadFileException Throws if unable to read file
     * @throws UnableToDecodeJSONException Throws if unable to decode json
     */
    public function read(string $path): array;

    /**
     *
     * Enable notification on start reading file
     *
     * @param string $message Message to print
     */
    public function notifyStartReading(string $message): self;

    /**
     *
     * Enable notification on reading and decoding json ends successfully
     *
     * @param string $message Message to print
     */
    public function notifyReadSuccessfully(string $message): self;

    /**
     *
     * Enable notifications on start reading file and on reading and decoding json ends successfully
     *
     * @param string $startMessage Message to print on start
     * @param string $successMessage Message to print on success
     */
    public function notifyStartAndSuccess(string $startMessage, string $successMessage): self;
}