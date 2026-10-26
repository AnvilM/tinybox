<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Syntax;

final readonly class Token
{
    /**
     * @param TokenType $type
     * @param string $text Token text; for quoted values - the unescaped content without quotes
     * @param int $position Character offset of the token start in the source expression
     */
    public function __construct(
        public TokenType $type,
        public string    $text,
        public int       $position,
    )
    {
    }
}
