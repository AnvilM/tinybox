<?php

declare(strict_types=1);

namespace App\Application\Outbound\Urltest;

use App\Domain\Outbound\VO\Urltest\UrltestParametersVO;

/**
 * Urltest group definition with everything that is known before the pipeline starts:
 * the resulting tag and the parameters from the templates.
 */
final readonly class PreparedUrltestGroup
{
    public function __construct(
        public UrltestGroupDefinition $definition,
        public string                 $tag,
        public UrltestParametersVO    $parameters,
    )
    {
    }
}
