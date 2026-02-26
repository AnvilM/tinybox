<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\OS;

use App\Core\Shared\Exception\CriticalException;
use RuntimeException;

abstract readonly class AbstractOSHelper
{
    protected OS $OS;

    public function __construct()
    {
        $this->OS = match (PHP_OS_FAMILY) {
            'Linux' => OS::Linux,
            'Windows' => OS::Windows,
            'Darwin' => OS::Darwin,
            default => throw new CriticalException("Unsupported OS: " . PHP_OS_FAMILY),
        };
    }

    /**
     * Execute help method
     *
     * @throws RuntimeException
     */
    public function execute(mixed ...$args)
    {
        return match ($this->OS) {
            OS::Linux => $this->executeLinux($args),
//            OS::Windows => $this->executeWindows($args),
//            OS::Darwin => $this->executeDarwin($args),
        };
    }

    abstract protected function executeLinux(mixed ...$args);

//    abstract protected function executeWindows(mixed ...$args);
//
//    abstract protected function executeDarwin(mixed ...$args);
}