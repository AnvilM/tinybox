<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\IO\Reporter\Output;

use Application\Config\ApplicationConfig\ApplicationConfig;
use League\CLImate\CLImate;

final readonly class CLI
{
    public function __construct(
        private CLImate $CLImate
    )
    {
    }

    /**
     * Prints message to stderr
     *
     * @param string|null $message Message to print
     * @param string|null $debugMessage Debug message to print
     *
     * @return CLI
     */
    public function err(?string $message = null, ?string $debugMessage = null): CLI
    {
        if ($message) $this->CLImate->to('error')->out($message);

        if (!ApplicationConfig::isDebug()) return $this;
        if ($debugMessage) $this->CLImate->to('error')->out($debugMessage);

        return $this;
    }

    /**
     * Prints message to stdout
     *
     * @param string|null $message Message to print
     * @param string|null $debugMessage Debug message to print
     *
     * @return CLI
     */
    public function out(?string $message = null, ?string $debugMessage = null): CLI
    {
        if ($message) $this->CLImate->out($message);

        if (!ApplicationConfig::isDebug()) return $this;
        if ($debugMessage) $this->CLImate->out($debugMessage);

        return $this;
    }

    /**
     * Prints br
     *
     * @param int $count Count of br's
     *
     * @return CLI
     */
    public function br(int $count = 1): CLI
    {
        if ($count > 0) $this->CLImate->br($count);

        return $this;
    }
}