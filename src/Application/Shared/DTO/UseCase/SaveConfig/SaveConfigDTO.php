<?php

declare(strict_types=1);

namespace App\Application\Shared\DTO\UseCase\SaveConfig;

final readonly class SaveConfigDTO
{
    /**
     * @param string $config Sing-box config as json
     */
    public function __construct(
        public string $config
    )
    {
    }
}