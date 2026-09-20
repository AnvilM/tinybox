<?php

declare(strict_types=1);

namespace App\Commands\Shared\Options;

use App\Application\Outbound\Filter\Criteria\CountryCodeCriteria;
use App\Application\Outbound\Filter\Criteria\CountryCodeExcludeCriteria;
use App\Application\Outbound\Filter\Criteria\TagExcludeCriteria;
use App\Application\Outbound\Filter\Criteria\TypeCriteria;
use App\Application\Outbound\Filter\Criteria\TypeExcludeCriteria;
use Iva\Input\Input;
use Iva\Input\Option;
use LogicException;
use Psl\Collection\Vector;

/**
 * The full outbound-filter option block (country code / type / name
 * filters, each with its own "except" and, where relevant, "only
 * available" flag). Direct port of OutboundFilterOptionsGroup — see
 * BaseOptionsTrait's docblock for why this is a trait rather than an
 * object registered on a registry, and OutboundFilterOptionHandles for why
 * a prefixed copy of the block needs its own bag of typed handles.
 *
 * A command that needs a second, prefixed copy of the block (Export's
 * urltest outbounds) calls configureOutboundFilterOptions() with
 * includeUrltestVariant: true, then resolves either variant independently:
 *
 *   $main    = $this->resolveOutboundFilterCriteria($input);
 *   $urltest = $this->resolveOutboundFilterCriteria($input, self::URLTEST_FILTER_PREFIX);
 */
trait OutboundFilterOptionsTrait
{
    /** @var array<string, OutboundFilterOptionHandles> */
    private array $outboundFilterOptionHandles = [];

    protected function configureOutboundFilterOptions(
        bool   $includeUrltestVariant = false,
        string $urltestPrefix = 'urltest',
    ): void
    {
        $this->outboundFilterOptionHandles[''] = $this->buildOutboundFilterOptionsBlock('');

        if ($includeUrltestVariant) {
            $this->outboundFilterOptionHandles[$urltestPrefix] = $this->buildOutboundFilterOptionsBlock($urltestPrefix);
        }
    }

    private function buildOutboundFilterOptionsBlock(string $prefix): OutboundFilterOptionHandles
    {
        $name = fn(string $option): string => $this->outboundFilterOptionName($prefix, $option);

        return new OutboundFilterOptionHandles(
        // 1. By country code (keep only), with its own except + own onlyAvailable flag
            countryCode: $this->addOption(Option::strings(
                name: $name('countryCode'),
                description: 'Keep only outbounds whose country code matches one of the specified codes',
            )),
            countryCodeExcept: $this->addOption(Option::strings(
                name: $name('countryCodeExcept'),
                description: sprintf(
                    'Outbound tags that are never dropped by --%s (they are still subject to every other filter)',
                    $name('countryCode'),
                ),
            )),
            countryOnlyAvailable: $this->addOption(Option::flag(
                name: $name('countryOnlyAvailable'),
                description: sprintf('With --%s, also exclude outbounds whose country code could not be resolved', $name('countryCode')),
            )),
            // 2. Exclude by country code, with its own except + own onlyAvailable flag (independent of #1)
            excludeCountryCode: $this->addOption(Option::strings(
                name: $name('excludeCountryCode'),
                description: 'Exclude outbounds whose country code matches one of the specified codes',
            )),
            excludeCountryCodeExcept: $this->addOption(Option::strings(
                name: $name('excludeCountryCodeExcept'),
                description: sprintf(
                    'Outbound tags that are never dropped by --%s (they are still subject to every other filter)',
                    $name('excludeCountryCode'),
                ),
            )),
            excludeCountryOnlyAvailable: $this->addOption(Option::flag(
                name: $name('excludeCountryOnlyAvailable'),
                description: sprintf('With --%s, also exclude outbounds whose country code could not be resolved', $name('excludeCountryCode')),
            )),
            // Shared country-code resolution setting for both #1 and #2 above
            countryOutboundIpFallback: $this->addOption(Option::flag(
                name: $name('countryOutboundIpFallback'),
                description: 'Use the outbound IP specified in the configuration if its real IP could not be obtained',
            )),
            // 3. By type (keep only), with its own except
            outboundType: $this->addOption(Option::strings(
                name: $name('outboundType'),
                description: 'Keep only outbounds whose type matches one of the specified types',
            )),
            outboundTypeExcept: $this->addOption(Option::strings(
                name: $name('outboundTypeExcept'),
                description: sprintf(
                    'Outbound tags that are never dropped by --%s (they are still subject to every other filter)',
                    $name('outboundType'),
                ),
            )),
            // 4. Exclude by type, with its own except
            excludeOutboundType: $this->addOption(Option::strings(
                name: $name('excludeOutboundType'),
                description: 'Exclude outbounds whose type matches one of the specified types',
            )),
            excludeOutboundTypeExcept: $this->addOption(Option::strings(
                name: $name('excludeOutboundTypeExcept'),
                description: sprintf(
                    'Outbound tags that are never dropped by --%s (they are still subject to every other filter)',
                    $name('excludeOutboundType'),
                ),
            )),
            // 5. By name
            excludeOutbound: $this->addOption(Option::strings(
                name: $name('excludeOutbound'),
                shortcut: $prefix === '' ? 'e' : null,
                description: 'One or more outbound tags to exclude by name',
            )),
            // 6. Global bypass - skips every filter above entirely for the listed tags
            exceptOutbound: $this->addOption(Option::strings(
                name: $name('exceptOutbound'),
                description: 'One or more outbound tags that will be ignored by all filters above and always kept',
            )),
        );
    }

    private function outboundFilterOptionName(string $prefix, string $name): string
    {
        return $prefix === '' ? $name : $prefix . ucfirst($name);
    }

    protected function resolveOutboundFilterCriteria(Input $input, string $prefix = ''): OutboundFilterCriteriaBag
    {
        $handles = $this->outboundFilterOptionHandles[$prefix] ?? throw new LogicException(sprintf(
            'Outbound filter options for prefix "%s" were never configured. Call configureOutboundFilterOptions() from configureCommand() first.',
            $prefix,
        ));

        $criteria = [];

        if ($excludeOutbound = $input->option($handles->excludeOutbound)) {
            $criteria[] = new TagExcludeCriteria(new Vector($excludeOutbound));
        }

        if ($excludeCountryCode = $input->option($handles->excludeCountryCode)) {
            $excludeCountryCodeExcept = $input->option($handles->excludeCountryCodeExcept);

            $criteria[] = new CountryCodeExcludeCriteria(
                new Vector($excludeCountryCode),
                $input->flag($handles->countryOutboundIpFallback),
                $input->flag($handles->excludeCountryOnlyAvailable),
                $excludeCountryCodeExcept !== [] ? new Vector($excludeCountryCodeExcept) : null,
            );
        }

        if ($countryCode = $input->option($handles->countryCode)) {
            $countryCodeExcept = $input->option($handles->countryCodeExcept);

            $criteria[] = new CountryCodeCriteria(
                new Vector($countryCode),
                $input->flag($handles->countryOutboundIpFallback),
                $input->flag($handles->countryOnlyAvailable),
                $countryCodeExcept !== [] ? new Vector($countryCodeExcept) : null,
            );
        }

        if ($excludeOutboundType = $input->option($handles->excludeOutboundType)) {
            $excludeOutboundTypeExcept = $input->option($handles->excludeOutboundTypeExcept);

            $criteria[] = new TypeExcludeCriteria(
                new Vector($excludeOutboundType),
                $excludeOutboundTypeExcept !== [] ? new Vector($excludeOutboundTypeExcept) : null,
            );
        }

        if ($outboundType = $input->option($handles->outboundType)) {
            $outboundTypeExcept = $input->option($handles->outboundTypeExcept);

            $criteria[] = new TypeCriteria(
                new Vector($outboundType),
                $outboundTypeExcept !== [] ? new Vector($outboundTypeExcept) : null,
            );
        }

        $exceptOutbound = $input->option($handles->exceptOutbound);

        return new OutboundFilterCriteriaBag(
            new Vector($criteria),
            $exceptOutbound !== [] ? new Vector($exceptOutbound) : null,
        );
    }
}
