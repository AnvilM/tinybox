<?php

declare(strict_types=1);

namespace App\Commands\Shared\Options\Pipeline;

/**
 * Kinds of the flags that form the ordered outbound pipeline.
 *
 * Option names are declared here once: the option traits and PipelineFlagOrder
 * take them from this enum, so the list of flags and the order reader never diverge.
 */
enum PipelineKind: string
{
    case Override = 'override';
    case Urltest = 'urltest';
    case Detour = 'detour';

    /**
     * Long name of the option, without dashes
     */
    public function long(): string
    {
        return $this->value;
    }

    /**
     * Shortcut of the option, without dash, null if there is none
     */
    public function short(): ?string
    {
        return match ($this) {
            self::Override => null,
            self::Urltest => 'u',
            self::Detour => 'd',
        };
    }
}
