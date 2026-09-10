<?php

declare(strict_types=1);

namespace App\Infrastructure\Outbound\Parser\RawOutboundParser\Utils;

enum TagEncodingType
{
    case BASE64;
    case URL_ENCODED;
    case PLAIN_TEXT;
}