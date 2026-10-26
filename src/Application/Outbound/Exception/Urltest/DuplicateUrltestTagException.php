<?php

declare(strict_types=1);

namespace App\Application\Outbound\Exception\Urltest;

use App\Domain\Shared\Exception\CriticalException;

final class DuplicateUrltestTagException extends CriticalException
{
    private const string HINT = 'Specify a unique tag: --urltest TAG:EXPRESSION';

    /**
     * @param string $tag Resulting urltest tag
     * @param bool $collidesWithOutbound True if the tag is taken by a regular outbound of the config,
     *                                   false if it is used by more than one urltest
     */
    public function __construct(string $tag, bool $collidesWithOutbound = false)
    {
        parent::__construct(
            $collidesWithOutbound
                ? sprintf("Urltest tag '%s' is already used by an outbound of the config. %s", $tag, self::HINT)
                : sprintf("Urltest tag '%s' is used more than once. %s", $tag, self::HINT),
        );
    }
}
