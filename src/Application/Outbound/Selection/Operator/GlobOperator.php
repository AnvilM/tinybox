<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Operator;

use App\Application\Outbound\Selection\Property\ValueKind;
use Closure;

/**
 * `prop ~ pattern` - the value matches any glob pattern
 * (`*` - any sequence of characters, `?` - exactly one character).
 *
 * NOTE: aliases are not expanded (`type~ss` does not match `shadowsocks`).
 */
final readonly class GlobOperator implements Operator
{
    public function symbol(): string
    {
        return '~';
    }

    public function supports(ValueKind $kind): bool
    {
        return $kind === ValueKind::String;
    }

    public function operandsAreValues(): bool
    {
        return false;
    }

    public function matcher(array $operands): Closure
    {
        $regexps = array_map(fn(string|int $pattern): string => $this->toRegexp((string)$pattern), $operands);

        return static function (string|int $value) use ($regexps): bool {
            foreach ($regexps as $regexp) {
                if (preg_match($regexp, (string)$value) === 1) {
                    return true;
                }
            }

            return false;
        };
    }

    private function toRegexp(string $pattern): string
    {
        $body = '';

        foreach (mb_str_split($pattern, 1, 'UTF-8') as $char) {
            $body .= match ($char) {
                '*' => '.*',
                '?' => '.',
                default => preg_quote($char, '/'),
            };
        }

        return '/\A' . $body . '\z/su';
    }
}
