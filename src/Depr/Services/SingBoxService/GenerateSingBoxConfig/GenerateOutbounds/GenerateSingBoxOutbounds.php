<?php

declare(strict_types=1);

namespace App\Core\Services\SingBoxService\GenerateSingBoxConfig\GenerateOutbounds;

use App\Core\Collections\Scheme\Collection\SchemeCollection;
use App\Core\Exceptions\ApplicationException;
use App\Infrastructure\Shared\CLI\Output;
use Application\Config\SingBoxConfig\SingBoxConfig;
use JsonException;
use RuntimeException;

final readonly class GenerateSingBoxOutbounds
{
    public function __construct(
        private GenerateSingBoxUrlTestOutbound $generateSingBoxUrlTestOutbound
    )
    {
    }

    /**
     * @param SchemeCollection $schemes
     * @return array Array of sing-box outbounds
     * @throws ApplicationException
     * @throws RuntimeException
     */
    public function generateSingBoxOutbounds(SchemeCollection $schemes): array
    {
        $path = SingBoxConfig::singBoxOutboundTemplatePath();

        Output::out(
            "<blue>[*] Loading sing-box outbound template...</blue>",
            "Loading from $path",
        )->br();

        $templateContent = @file_get_contents($path);

        if ($templateContent === false) throw new ApplicationException("Unable to read sing box outbound template file at $path");

        try {
            $templateContent = json_decode($templateContent, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new ApplicationException("Unable to parse JSON: " . $e->getMessage());
        }

        $outbounds = [];
        $tags = [];

        foreach ($schemes as $scheme) {
            $outbound = [
                'type' => $scheme->type->value,
                'tag' => $scheme->tag,
                'server' => $scheme->server,
                'server_port' => $scheme->port,
                'uuid' => $scheme->uuid,
                'tls' => [
                    'enabled' => true,
                    'server_name' => $scheme->sni,
                    'reality' => [
                        'enabled' => true,
                        'public_key' => $scheme->pbk,
                        'short_id' => $scheme->sid,
                    ]
                ]
            ];

            if ($scheme->flow) $outbound['flow'] = $scheme->flow;
            if ($scheme->fp) $outbound['utls'] = [
                'enabled' => true,
                'fingerprint' => $scheme->fp,
            ];

            $tags[] = $scheme->tag;

            $outbounds[] = array_merge($templateContent, $outbound);
        }

        $outbounds[] = $this->generateSingBoxUrlTestOutbound->generateSingBoxUrlTestOutbound($tags);

        return $outbounds;
    }
}