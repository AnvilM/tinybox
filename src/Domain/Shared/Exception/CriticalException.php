<?php

declare(strict_types=1);

namespace App\Domain\Shared\Exception;

use App\Domain\Shared\ReporterEvent\ReporterEvent;

class CriticalException extends CoreException
{
    /**
     * @var ReporterEvent[]
     */
    public ?array $events = null;

    public function __construct(string $message = "", public ?string $debugMessage = null)
    {
        parent::__construct($message);
    }

    public static function fromEvents(ReporterEvent ...$events): static
    {
        return new static()->setEvents(...$events);
    }

    private function setEvents(ReporterEvent ...$events): static
    {
        $this->events = $events;
        return $this;
    }

}