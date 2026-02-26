<?php

declare(strict_types=1);

namespace App\Core\Shared\VO\Config;

use App\Core\Shared\VO\Config\SingBox\SingBoxConfigVO;

final readonly  class ConfigVO
{
    public function __construct(
        public string          $subscriptionListPath,
        public string          $generatedConfigsDirectoryPath,
        public SingBoxConfigVO $singBoxConfig,
    )
    {
    }
}