<?php

declare(strict_types=1);

namespace App\Application\Shared\Scheme\CreateSchemeEntityFromString\Parser\Utils;

enum TagEncodingType
{
    case BASE64;
    case URL_ENCODED;
    case PLAIN_TEXT;
}