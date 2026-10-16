<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override;

use App\Application\Outbound\Override\ResolveOutboundDNS\OverrideResolveOutboundDNS;
use App\Application\Outbound\Override\SSPass\OverrideSSPass;
use App\Application\Outbound\Override\TlsInsecure\OverrideTlsInsecure;
use App\Application\Outbound\Override\TLSSNI\OverrideTLSSNI;
use App\Application\Outbound\Override\VerifyPeerCertByNameFromSni\OverrideVerifyPeerCertByNameFromSni;
use App\Application\Outbound\Override\VlessUUID\OverrideVlessUUID;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Ports\DNS\DnsResolverPort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterInstancePort;
use InvalidArgumentException;
use LogicException;
use Psl\Collection\Vector;

final readonly class OverrideOutboundService
{
    /** @var array<class-string<OverrideType>, Override> */
    private array $overrides;

    public function __construct(DnsResolverPort $dnsResolverPort, ReporterInstancePort $reporterInstancePort)
    {
        $this->overrides = self::indexByType([
            new OverrideResolveOutboundDNS($dnsResolverPort, $reporterInstancePort),
            new OverrideVerifyPeerCertByNameFromSni(),
            new OverrideVlessUUID(),
            new OverrideTlsInsecure(),
            new OverrideTLSSNI(),
            new OverrideSSPass(),
        ]);
    }

    /**
     * @param list<Override> $overrides
     *
     * @return array<class-string<OverrideType>, Override>
     */
    private static function indexByType(array $overrides): array
    {
        $indexed = [];

        foreach ($overrides as $override) {
            $typeClass = $override->getTypeClass();

            if (isset($indexed[$typeClass])) {
                throw new LogicException(
                    sprintf('More than one override registered for type %s.', $typeClass)
                );
            }

            $indexed[$typeClass] = $override;
        }

        return $indexed;
    }

    /**
     * @template T of OutboundMap
     *
     * @param T $outbounds Outbounds to override
     * @param Vector<OverrideType> $types Override types, applied in the given order
     *
     * @return T Outbounds map with overridden outbounds
     *
     * @throws InvalidArgumentException If there is no Override for one of the given types
     */
    public function override(OutboundMap $outbounds, Vector $types): OutboundMap
    {
        if ($types->isEmpty()) return $outbounds;

        foreach ($types as $type) {
            $override = $this->overrides[$type::class]
                ?? throw new InvalidArgumentException(
                    sprintf('No override registered for type %s.', $type::class)
                );

            $outbounds = $override->override($outbounds, $type);
        }

        return $outbounds;
    }
}
