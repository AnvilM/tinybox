<?php

declare(strict_types=1);

namespace App\Application\Shared\DTO\UseCase\SaveSingBoxConfig;

final readonly class SaveSingBoxConfigDTO
{
    /**
     * @param string $singBoxConfig Sing-box config as json
     */
    public function __construct(
        public string $singBoxConfig
    )
    {
    }
}