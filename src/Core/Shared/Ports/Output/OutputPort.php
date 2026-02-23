<?php

declare(strict_types=1);

namespace App\Core\Shared\Ports\Output;

interface OutputPort
{
    /**
     * Prints message to stdout
     *
     * @param string|null $message Message to print
     * @param string|null $debugMessage Debug message to print
     *
     * @return OutputPort
     */
    public function out(?string $message = null, ?string $debugMessage = null): OutputPort;


    /**
     * Prints message to stderr
     *
     * @param string|null $message Message to print
     * @param string|null $debugMessage Debug message to print
     *
     * @return OutputPort
     */
    public function err(?string $message = null, ?string $debugMessage = null): OutputPort;

    /**
     * Prints br
     *
     * @param int $count Count of br's
     *
     * @return OutputPort
     */
    public function br(int $count = 1): OutputPort;

}