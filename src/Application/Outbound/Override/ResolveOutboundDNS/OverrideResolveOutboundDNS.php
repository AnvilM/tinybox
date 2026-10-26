<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override\ResolveOutboundDNS;

use App\Application\Outbound\Override\Override;
use App\Application\Outbound\Override\OverrideType;
use App\Domain\Interface\Outbound\ServerProvider;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Ports\DNS\DnsResolverPort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterInstancePort;
use App\Domain\Shared\ReporterEvent\ReporterEventBuilder;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventAttachmentVO;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventVerbosity;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use InvalidArgumentException;
use Psl\Async;

/** @extends Override<OverrideTypeResolveOutboundDNS> */
final readonly class OverrideResolveOutboundDNS extends Override
{
    public function __construct(private DnsResolverPort $dnsResolverPort, private ReporterInstancePort $reporterInstancePort)
    {
    }

    public function getTypeClass(): string
    {
        return OverrideTypeResolveOutboundDNS::class;
    }

    public function name(): string
    {
        return 'resolve-dns';
    }

    public function createType(?string $value): OverrideType
    {
        $this->rejectValue($value);

        return new OverrideTypeResolveOutboundDNS();
    }

    /** @param OverrideTypeResolveOutboundDNS $type */
    protected function apply(OutboundMap $outboundMap, OverrideType $type): OutboundMap
    {
        /**
         * Create array of outbounds domains
         */
        $domains = $this->collectDomains($outboundMap);


        /**
         * Check if outbounds domains array is not empty
         */
        if ($domains === []) return $outboundMap;


        /**
         * Asunc resolve outbounds domain names
         */
        $tasks = [];
        foreach ($domains as $domain) {
            $tasks[$domain] = fn(): ?string => $this->dnsResolverPort->resolveDoH($domain);
        }

        /** @var array<string, ?string> $resolved */
        $resolved = Async\concurrently($tasks);


        /**
         * Create empty outbounds map
         */
        $outbounds = $outboundMap->createEmpty();


        /**
         * Update outbounds server fields
         */
        foreach ($outboundMap->getOutbounds() as $outbound) {

            /**
             * Check if outbound is server provider
             */
            if (!($outbound instanceof ServerProvider)) {
                $outbounds->add($outbound);
                continue;
            }


            /**
             * Try to create NonEmptyString with resolved ip
             */
            try {
                $ip = new NonEmptyStringVO($resolved[$outbound->getServer()->getValue()] ?? null);
            } catch (InvalidArgumentException) {
                /**
                 * Use default outbound if unable to create NonEmptyString with resolved ip
                 * (actually if ip is not found in array e.g. domain is not resolved)
                 */
                $outbounds->add($outbound);

                $this->reporterInstancePort->get()->notify(ReporterEventBuilder::warning(
                    "Unable to resolve domain for \"{$outbound->getTagString()}\" outbound",
                )->attachments(
                    new ReporterEventAttachmentVO(
                        "Domain: {$outbound->getServerString()}",
                        ReporterEventVerbosity::VeryVerbose)
                )->verbose());
                
                continue;
            }


            $outbounds->add($outbound->withServer($ip));
        }

        return $outbounds;
    }

    /** @return list<string> */
    private function collectDomains(OutboundMap $outboundMap): array
    {
        $domains = [];

        foreach ($outboundMap->getOutbounds() as $outbound) {
            if (!($outbound instanceof ServerProvider)) {
                continue;
            }

            $server = $outbound->getServer()->getValue();

            if (filter_var($server, FILTER_VALIDATE_IP) === false) {
                $domains[$server] = $server;
            }
        }

        return array_values($domains);
    }
}