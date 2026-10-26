<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override;

use App\Application\Outbound\Exception\Override\UnknownOverrideException;
use App\Application\Outbound\Override\ResolveOutboundDNS\OverrideResolveOutboundDNS;
use App\Application\Outbound\Override\SSPass\OverrideSSPass;
use App\Application\Outbound\Override\TlsInsecure\OverrideTlsInsecure;
use App\Application\Outbound\Override\TLSSNI\OverrideTLSSNI;
use App\Application\Outbound\Override\VerifyPeerCertByNameFromSni\OverrideVerifyPeerCertByNameFromSni;
use App\Application\Outbound\Override\VlessUUID\OverrideVlessUUID;
use LogicException;

/**
 * Central place that knows every override.
 *
 * The constructor lists concrete classes (not `iterable`), so PHP-DI autowires
 * the registry without any manual container definition.
 *
 * To add a new override:
 *  1. Create a pair Override* + OverrideType* like the existing ones, implement `name()` and `createType()`.
 *  2. Add the Override as a typed constructor parameter here and to the list below.
 */
final readonly class OverrideRegistry
{
    /** @var array<string, Override> */
    private array $byName;

    /** @var array<class-string<OverrideType>, Override> */
    private array $byType;

    public function __construct(
        OverrideResolveOutboundDNS          $resolveDns,
        OverrideVerifyPeerCertByNameFromSni $verifyPeerCertByNameFromSni,
        OverrideVlessUUID                   $vlessUuid,
        OverrideTlsInsecure                 $tlsInsecure,
        OverrideTLSSNI                      $tlsSni,
        OverrideSSPass                      $ssPass,
    )
    {
        $byName = [];
        $byType = [];

        foreach ([$resolveDns, $verifyPeerCertByNameFromSni, $vlessUuid, $tlsInsecure, $tlsSni, $ssPass] as $override) {
            $name = $override->name();
            $typeClass = $override->getTypeClass();

            if (isset($byName[$name])) {
                throw new LogicException(sprintf("More than one override registered with name '%s'.", $name));
            }

            if (isset($byType[$typeClass])) {
                throw new LogicException(sprintf('More than one override registered for type %s.', $typeClass));
            }

            $byName[$name] = $override;
            $byType[$typeClass] = $override;
        }

        $this->byName = $byName;
        $this->byType = $byType;
    }

    /**
     * @param string $name Lower-case override name
     * @param string $source Original SPEC string, used in the error message
     *
     * @throws UnknownOverrideException
     */
    public function byName(string $name, string $source): Override
    {
        return $this->byName[$name]
            ?? throw new UnknownOverrideException($name, $this->names(), $this->suggest($name), $source);
    }

    /**
     * @param class-string<OverrideType> $typeClass
     *
     * @throws LogicException If there is no Override for the type
     */
    public function byType(string $typeClass): Override
    {
        return $this->byType[$typeClass]
            ?? throw new LogicException(sprintf('No override registered for type %s.', $typeClass));
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        return array_keys($this->byName);
    }

    private function suggest(string $name): ?string
    {
        $best = null;
        $bestDistance = 3;

        foreach ($this->names() as $candidate) {
            $distance = levenshtein($name, $candidate);

            if ($distance <= 2 && $distance < $bestDistance) {
                $best = $candidate;
                $bestDistance = $distance;
            }
        }

        return $best;
    }
}
