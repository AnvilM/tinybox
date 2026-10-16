<?php

declare(strict_types=1);

namespace App\Commands\Shared\Options;

use App\Application\Outbound\Override\Overrides\OverrideSSPass;
use App\Application\Outbound\Override\Overrides\OverrideTlsInsecure;
use App\Application\Outbound\Override\Overrides\OverrideTLSSNI;
use App\Application\Outbound\Override\Overrides\OverrideVerifyPeerCertByNameFromSni;
use App\Application\Outbound\Override\Overrides\OverrideVlessUUID;
use App\Application\Outbound\Override\OverrideType;
use App\Application\Outbound\Override\ResolveOutboundDNS\OverrideTypeResolveOutboundDNS;
use App\Application\Outbound\Override\SSPass\OverrideTypeSSPass;
use App\Application\Outbound\Override\TlsInsecure\OverrideTypeTlsInsecure;
use App\Application\Outbound\Override\TLSSNI\OverrideTypeTLSSNI;
use App\Application\Outbound\Override\VerifyPeerCertByNameFromSni\OverrideTypeVerifyPeerCertByNameFromSni;
use App\Application\Outbound\Override\VlessUUID\OverrideTypeVlessUUID;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use Iva\Input\Input;
use Iva\Input\Option;
use Psl\Collection\Vector;

/**
 * Direct port of OverridesOptionsGroup — see BaseOptionsTrait's docblock
 * for why this is a trait rather than an object registered on a registry.
 */
trait OverridesOptionsTrait
{
    private Option $overrideVlessUuidOption;
    private Option $overrideSsPassOption;
    private Option $overrideTlsSniOption;
    private Option $overrideTlsInsecureOption;
    private Option $overrideTlsVerifyPeerCertByNameFromSniOption;
    private Option $overrideResolveOutboundDnsOption;

    protected function configureOverridesOptions(): void
    {
        $this->overrideVlessUuidOption = $this->addOption(Option::string(
            name: 'override-vless-uuid',
            description: 'Overrides vless uuid',
        ));

        $this->overrideSsPassOption = $this->addOption(Option::string(
            name: 'override-ss-pass',
            description: 'Overrides shadowsocks password',
        ));

        $this->overrideTlsSniOption = $this->addOption(Option::string(
            name: 'override-tls-sni',
            description: 'Overrides tls sni',
        ));

        $this->overrideTlsInsecureOption = $this->addOption(Option::string(
            name: 'override-tls-insecure',
            description: 'Overrides tls insecure e.g. --override-tls-insecure="true"',
        ));

        $this->overrideTlsVerifyPeerCertByNameFromSniOption = $this->addOption(Option::flag(
            name: 'override-tls-verify-peer-cert-by-name-from-sni',
            description: 'Overrides tls verify peer certificate by name with value from sni field',
        ));

        $this->overrideResolveOutboundDnsOption = $this->addOption(Option::flag(
            name: 'override-resolve-outbound-dns',
            description: 'Overrides outbound server address with resolved ip from outbound domain name',
        ));
    }

    /**
     * @return Vector<OverrideType>
     */
    protected function resolveOverrideTypes(Input $input): Vector
    {
        $overrides = [];

        if ($input->flag($this->overrideTlsVerifyPeerCertByNameFromSniOption)) {
            $overrides[] = new OverrideTypeVerifyPeerCertByNameFromSni();
        }

        if ($input->flag($this->overrideResolveOutboundDnsOption)) {
            $overrides[] = new OverrideTypeResolveOutboundDNS();
        }

        if ($this->resolveOverrideUUID($input)) $overrides[] = new OverrideTypeVlessUUID($this->resolveOverrideUUID($input));
        if ($this->resolveOverrideTlsSni($input)) $overrides[] = new OverrideTypeTLSSNI($this->resolveOverrideTlsSni($input));
        if ($this->resolveOverrideSSPass($input)) $overrides[] = new OverrideTypeSSPass($this->resolveOverrideSSPass($input));
        if ($this->resolveOverrideTlsInsecure($input) !== null) $overrides[] = new OverrideTypeTlsInsecure($this->resolveOverrideTlsInsecure($input));

        return new Vector($overrides);
    }

    protected function resolveOverrideUUID(Input $input): ?NonEmptyStringVO
    {
        $value = $input->option($this->overrideVlessUuidOption);
        return $value === null ? null : new NonEmptyStringVO($value);
    }

    protected function resolveOverrideTlsSni(Input $input): ?NonEmptyStringVO
    {
        $value = $input->option($this->overrideTlsSniOption);
        return $value === null ? null : new NonEmptyStringVO($value);
    }

    protected function resolveOverrideSSPass(Input $input): ?NonEmptyStringVO
    {
        $value = $input->option($this->overrideSsPassOption);
        return $value === null ? null : new NonEmptyStringVO($value);
    }

    protected function resolveOverrideTlsInsecure(Input $input): ?bool
    {
        $value = $input->option($this->overrideTlsInsecureOption);
        return $value === null ? null : $value === "true";
    }
}
