<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Config;

use App\Domain\Shared\VO\Config\SingBox\SingBoxConfigVO;

final readonly  class ConfigVO
{
    public function __construct(
        public string          $subscriptionListPath,
        public string          $generatedConfigsDirectoryPath,
        public string          $schemesListPath,
        public SingBoxConfigVO $singBoxConfig,
    )
    {
    }
}