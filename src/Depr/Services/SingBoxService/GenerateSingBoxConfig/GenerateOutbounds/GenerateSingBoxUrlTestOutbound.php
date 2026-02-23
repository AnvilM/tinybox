<?php

declare(strict_types=1);

namespace App\Core\Services\SingBoxService\GenerateSingBoxConfig\GenerateOutbounds;

use App\Core\Exceptions\ApplicationException;
use App\Infrastructure\Shared\CLI\Output;
use Application\Config\SingBoxConfig\SingBoxConfig;
use JsonException;

final readonly class GenerateSingBoxUrlTestOutbound
{
    /**
     * @param string[] $tags Tags from schemes
     * @return array Urltest outbound
     * @throws ApplicationException
     */
    public function generateSingBoxUrlTestOutbound(array $tags): array
    {
        $path = SingBoxConfig::singBoxUrltestOutboundTemplatePath();

        Output::out(
            "<blue>[*] Loading sing-box urltest outbound template...</blue>",
            "Loading from $path",
        )->br();

        $templateContent = @file_get_contents($path);

        if ($templateContent === false) throw new ApplicationException("Unable to read sing box urltest outbound template file at $path");

        try {
            $templateContent = json_decode($templateContent, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new ApplicationException("Unable to parse JSON: " . $e->getMessage());
        }


        foreach ($tags as $tag) {
            $templateContent['outbounds'][] = $tag;
        }

        return $templateContent;
    }
}