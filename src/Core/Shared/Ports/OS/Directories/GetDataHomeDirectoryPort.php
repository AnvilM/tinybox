<?php

declare(strict_types=1);

namespace App\Core\Shared\Ports\OS\Directories;

use RuntimeException;
use UnexpectedValueException;

interface GetDataHomeDirectoryPort
{
    /**
     * Returns the application's data home directory path.
     *
     * This method constructs the path based on the user's HOME environment
     * variable and the application name. It validates that the HOME
     * environment variable exists and is a string before building the path.
     *
     * @return string Full path to the application's data home directory
     *
     * @throws RuntimeException If the HOME environment variable is not set
     * @throws UnexpectedValueException If the HOME environment variable is not a string
     */
    public function execute(): string;
}