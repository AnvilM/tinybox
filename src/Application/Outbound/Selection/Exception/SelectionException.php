<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Exception;

use App\Domain\Shared\Exception\CriticalException;

/**
 * Base class for every error raised while parsing or compiling a selection
 * expression.
 *
 * The message is the short human readable reason, the debug message shows
 * the source expression with a caret under the offending position:
 *
 *     tag~RU-* & typ=ss
 *                ^
 *
 * NOTE: $position is a character offset (not a byte offset), so the caret
 * stays aligned for expressions containing non-ASCII characters.
 */
abstract class SelectionException extends CriticalException
{
    public function __construct(string $message, string $source, int $position)
    {
        parent::__construct($message, $source . "\n" . str_repeat(' ', max(0, $position)) . '^');
    }
}
