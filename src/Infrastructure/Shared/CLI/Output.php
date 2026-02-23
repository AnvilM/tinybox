<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\CLI;

use App\Core\Shared\Ports\Config\Application\ApplicationConfigPort;
use App\Core\Shared\Ports\Output\OutputPort;
use League\CLImate\CLImate;

final readonly class Output implements OutputPort
{
    public function __construct(
        private CLImate               $CLImate,
        private ApplicationConfigPort $ApplicationPort
    )
    {
    }

    public function err(?string $message = null, ?string $debugMessage = null): OutputPort
    {
        if ($message) $this->CLImate->to('error')->out($message);

        if (!$this->ApplicationPort::isDebug()) return $this;
        if ($debugMessage) $this->CLImate->to('error')->out($debugMessage);

        return $this;
    }

    public function out(?string $message = null, ?string $debugMessage = null): OutputPort
    {
        if ($message) $this->CLImate->out($message);

        if (!$this->ApplicationPort::isDebug()) return $this;
        if ($debugMessage) $this->CLImate->out($debugMessage);

        return $this;
    }

    public function br(int $count = 1): OutputPort
    {
        if ($count > 0) $this->CLImate->br($count);

        return $this;
    }
}