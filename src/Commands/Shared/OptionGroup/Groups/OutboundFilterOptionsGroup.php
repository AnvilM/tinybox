<?php

declare(strict_types=1);

namespace App\Commands\Shared\OptionGroup\Groups;

use App\Application\Outbound\Filter\Criteria\CountryCodeCriteria;
use App\Application\Outbound\Filter\Criteria\CountryCodeExcludeCriteria;
use App\Application\Outbound\Filter\Criteria\TagExcludeCriteria;
use App\Application\Outbound\Filter\Criteria\TypeCriteria;
use App\Application\Outbound\Filter\Criteria\TypeExcludeCriteria;
use App\Commands\Shared\OptionGroup\AbstractOptionGroup;
use Psl\Collection\Vector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * The full outbound-filter option block (country code / type / name filters,
 * each with its own "except" and, where relevant, "only available" flag).
 *
 * Registering it twice under different prefixes used to mean two separate
 * classes or two registry entries. Here it's the same instance and the same
 * option definitions - `configure()` optionally adds a second, prefixed
 * copy of the block (e.g. for urltest outbounds), and `resolve()` takes the
 * same prefix so a command can resolve either variant independently:
 *
 *   $group = $this->optionGroups->get(OutboundFilterOptionsGroup::class);
 *   $main    = $group->resolve($input);
 *   $urltest = $group->resolve($input, prefix: 'urltest');
 */
final readonly class OutboundFilterOptionsGroup extends AbstractOptionGroup
{
    public function __construct(
        private bool   $includeUrltestVariant = false,
        private string $urltestPrefix = 'urltest',
    )
    {
    }

    public function configure(Command $command): void
    {
        $this->configureBlock($command, '');

        if ($this->includeUrltestVariant) {
            $this->configureBlock($command, $this->urltestPrefix);
        }
    }

    private function configureBlock(Command $command, string $prefix): void
    {
        $name = fn(string $option): string => $this->optionName($prefix, $option);

        $command
            // 1. By country code (keep only), with its own except + own onlyAvailable flag
            ->addOption(
                $name('countryCode'),
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Keep only outbounds whose country code matches one of the specified codes',
            )
            ->addOption(
                $name('countryCodeExcept'),
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                sprintf(
                    "Outbound tags that are never dropped by --%s (they are still subject to every other filter)",
                    $name('countryCode'),
                ),
            )
            ->addOption(
                $name('countryOnlyAvailable'),
                null,
                InputOption::VALUE_NONE,
                sprintf("With --%s, also exclude outbounds whose country code could not be resolved", $name('countryCode')),
            )
            // 2. Exclude by country code, with its own except + own onlyAvailable flag (independent of #1)
            ->addOption(
                $name('excludeCountryCode'),
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Exclude outbounds whose country code matches one of the specified codes',
            )
            ->addOption(
                $name('excludeCountryCodeExcept'),
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                sprintf(
                    "Outbound tags that are never dropped by --%s (they are still subject to every other filter)",
                    $name('excludeCountryCode'),
                ),
            )
            ->addOption(
                $name('excludeCountryOnlyAvailable'),
                null,
                InputOption::VALUE_NONE,
                sprintf("With --%s, also exclude outbounds whose country code could not be resolved", $name('excludeCountryCode')),
            )
            // Shared country-code resolution setting for both #1 and #2 above
            ->addOption(
                $name('countryOutboundIpFallback'),
                null,
                InputOption::VALUE_NONE,
                'Use the outbound IP specified in the configuration if its real IP could not be obtained',
            )
            // 3. By type (keep only), with its own except
            ->addOption(
                $name('outboundType'),
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Keep only outbounds whose type matches one of the specified types',
            )
            ->addOption(
                $name('outboundTypeExcept'),
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                sprintf(
                    "Outbound tags that are never dropped by --%s (they are still subject to every other filter)",
                    $name('outboundType'),
                ),
            )
            // 4. Exclude by type, with its own except
            ->addOption(
                $name('excludeOutboundType'),
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Exclude outbounds whose type matches one of the specified types',
            )
            ->addOption(
                $name('excludeOutboundTypeExcept'),
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                sprintf(
                    "Outbound tags that are never dropped by --%s (they are still subject to every other filter)",
                    $name('excludeOutboundType'),
                ),
            )
            // 5. By name
            ->addOption(
                $name('excludeOutbound'),
                $prefix === '' ? 'e' : null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'One or more outbound tags to exclude by name',
            )
            // 6. Global bypass - skips every filter above entirely for the listed tags
            ->addOption(
                $name('exceptOutbound'),
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'One or more outbound tags that will be ignored by all filters above and always kept',
            );
    }

    private function optionName(string $prefix, string $name): string
    {
        return $prefix === '' ? $name : $prefix . ucfirst($name);
    }

    public function resolve(string $prefix = ''): OutboundFilterCriteriaBag
    {
        $get = fn(string $option): mixed => $this->input->getOption($this->optionName($prefix, $option));

        $criteria = [];

        if ($excludeOutbound = $get('excludeOutbound')) {
            $criteria[] = new TagExcludeCriteria(new Vector($excludeOutbound));
        }

        if ($excludeCountryCode = $get('excludeCountryCode')) {
            $excludeCountryCodeExcept = $get('excludeCountryCodeExcept');

            $criteria[] = new CountryCodeExcludeCriteria(
                new Vector($excludeCountryCode),
                (bool)$get('countryOutboundIpFallback'),
                (bool)$get('excludeCountryOnlyAvailable'),
                $excludeCountryCodeExcept ? new Vector($excludeCountryCodeExcept) : null,
            );
        }

        if ($countryCode = $get('countryCode')) {
            $countryCodeExcept = $get('countryCodeExcept');

            $criteria[] = new CountryCodeCriteria(
                new Vector($countryCode),
                (bool)$get('countryOutboundIpFallback'),
                (bool)$get('countryOnlyAvailable'),
                $countryCodeExcept ? new Vector($countryCodeExcept) : null,
            );
        }

        if ($excludeOutboundType = $get('excludeOutboundType')) {
            $excludeOutboundTypeExcept = $get('excludeOutboundTypeExcept');

            $criteria[] = new TypeExcludeCriteria(
                new Vector($excludeOutboundType),
                $excludeOutboundTypeExcept ? new Vector($excludeOutboundTypeExcept) : null,
            );
        }

        if ($outboundType = $get('outboundType')) {
            $outboundTypeExcept = $get('outboundTypeExcept');

            $criteria[] = new TypeCriteria(
                new Vector($outboundType),
                $outboundTypeExcept ? new Vector($outboundTypeExcept) : null,
            );
        }

        $exceptOutbound = $get('exceptOutbound');

        return new OutboundFilterCriteriaBag(
            new Vector($criteria),
            $exceptOutbound ? new Vector($exceptOutbound) : null,
        );
    }
}
