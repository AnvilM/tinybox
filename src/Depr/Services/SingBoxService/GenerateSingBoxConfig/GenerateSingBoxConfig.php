<?php

declare(strict_types=1);

namespace App\Core\Services\SingBoxService\GenerateSingBoxConfig;

use App\Core\Collections\Scheme\Collection\SchemeCollection;
use App\Core\Exceptions\ApplicationException;
use App\Core\Services\SingBoxService\GenerateSingBoxConfig\GenerateOutbounds\GenerateSingBoxOutbounds;
use App\Infrastructure\Shared\CLI\Output;
use Application\Config\SingBoxConfig\SingBoxConfig;
use JsonException;
use RuntimeException;

final readonly class GenerateSingBoxConfig
{
    public function __construct(
        private GenerateSingBoxOutbounds $generateSingBoxOutbounds,
    )
    {
    }

    /**
     * @param SchemeCollection $schemes
     * @return array Sing-box config
     * @throws ApplicationException
     * @throws RuntimeException
     */
    public function generateSingBoxConfig(SchemeCollection $schemes): array
    {
        $path = SingBoxConfig::singBoxConfigTemplatePath();

        Output::out(
            "<blue>[*] Loading sing-box config template...</blue>",
            "Loading from $path",
        )->br();

        $templateContent = @file_get_contents($path);

        if ($templateContent === false) throw new ApplicationException("Unable to read sing box config template file at $path");

        try {
            $templateContent = json_decode($templateContent, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new ApplicationException("Unable to parse JSON: " . $e->getMessage());
        }

        $templateContent['outbounds'] = $this->generateSingBoxOutbounds->generateSingBoxOutbounds($schemes);

        return $templateContent;
    }
}