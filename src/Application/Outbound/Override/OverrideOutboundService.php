<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override;

use App\Application\Outbound\Exception\Override\UnsupportedOverrideType;
use App\Application\Outbound\Override\Overrides\OverrideSSPass;
use App\Application\Outbound\Override\Overrides\OverrideTlsInsecure;
use App\Application\Outbound\Override\Overrides\OverrideTLSSNI;
use App\Application\Outbound\Override\Overrides\OverrideVerifyPeerCertByNameFromSni;
use App\Application\Outbound\Override\Overrides\OverrideVlessUUID;
use App\Application\Outbound\Override\OverrideTypes\OverrideTypeSSPass;
use App\Application\Outbound\Override\OverrideTypes\OverrideTypeTlsInsecure;
use App\Application\Outbound\Override\OverrideTypes\OverrideTypeTLSSNI;
use App\Application\Outbound\Override\OverrideTypes\OverrideTypeVerifyPeerCertByNameFromSni;
use App\Application\Outbound\Override\OverrideTypes\OverrideTypeVlessUUID;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Ports\IO\Reporter\ReporterInstancePort;
use App\Domain\Shared\ReporterEvent\ReporterEventBuilder;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventAttachmentVO;
use Psl\Collection\Vector;
use function Psl\Type\instance_of;

final readonly class OverrideOutboundService
{
    public function __construct(
        private ReporterInstancePort $reporter,
    )
    {
    }

    /**
     * @template T of OutboundMap
     *
     * @param T $outbounds Outbounds to override
     * @param Vector $overrideTypes Override types
     *
     * @return T Outbounds map with overridden outbounds
     */
    public function override(OutboundMap $outbounds, Vector $overrideTypes): OutboundMap
    {
        if ($overrideTypes->isEmpty()) return $outbounds;


        $outboundsMap = $outbounds->createEmpty();

        foreach ($outbounds->getOutbounds() as $outbound) {
            foreach ($overrideTypes as $overrideType) {
                try {
                    $outbound = $this->matchOverrides($overrideType)->override($outbound, $overrideType);
                } catch (UnsupportedOverrideType $e) {
                    $this->reporter->get()->notify(
                        ReporterEventBuilder::warning($e->getMessage())->attachments(
                            ReporterEventAttachmentVO::veryVerbose($e->getDebugMessage()),
                        )->verbose()
                    );
                }
            }

            $outboundsMap->add($outbound);
        }

        return $outboundsMap;
    }

    /**
     * @throws UnsupportedOverrideType
     */
    private function matchOverrides(OverrideType $overrideType): Override
    {
        return match (instance_of($overrideType::class)->toString()) {
            OverrideTypeSSPass::class => new OverrideSSPass(),
            OverrideTypeVlessUUID::class => new OverrideVlessUUID(),
            OverrideTypeTLSSNI::class => new OverrideTLSSNI(),
            OverrideTypeTlsInsecure::class => new OverrideTlsInsecure(),
            OverrideTypeVerifyPeerCertByNameFromSni::class => new OverrideVerifyPeerCertByNameFromSni(),
            default => throw new UnsupportedOverrideType($overrideType)
        };
    }
}