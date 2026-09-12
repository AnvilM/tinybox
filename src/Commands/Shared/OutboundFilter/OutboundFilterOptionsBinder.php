<?php

declare(strict_types=1);

namespace App\Commands\Shared\OutboundFilter;

use App\Application\Outbound\Filter\Criteria\CountryCodeCriteria;
use App\Application\Outbound\Filter\Criteria\CountryCodeExcludeCriteria;
use App\Application\Outbound\Filter\Criteria\TagExcludeCriteria;
use App\Application\Outbound\Filter\Criteria\TypeCriteria;
use App\Application\Outbound\Filter\Criteria\TypeExcludeCriteria;
use App\Commands\Shared\OutboundFilter\DTO\OutboundFilterCriteriaBag;
use Psl\Collection\Vector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Single place that knows every outbound-filtering CLI option and how it
 * maps onto a {@see OutboundFilterCriteriaBag}.
 *
 * ## Problem this solves
 *
 * Every command that filters outbounds (`subscription:apply`,
 * `subscription:test`, and ApplySubscriptionCommand's second, independent
 * urltest-only filter group) needs the exact same ~15 CLI options and the
 * exact same "if option is set, push a Criteria" logic. Without this class
 * that logic would have to be copy-pasted into `configure()` and `handle()`
 * of every such command (and kept in sync by hand forever).
 *
 * ## How it works
 *
 * `configure()` registers the options on a command, `resolve()` reads them
 * back into a {@see OutboundFilterCriteriaBag} ready to build a
 * `FilterOutboundsDTO` from. A command that needs outbound filtering just
 * calls both methods - it never lists a single `addOption()` for filtering
 * itself, and never changes when a new filter rule is added here.
 *
 * ## Prefixing (reusing the same options twice in one command)
 *
 * `ApplySubscriptionCommand` needs the *entire* filter rule set twice: once
 * for the main config outbounds, and once more, completely independently,
 * for the outbounds that go into the `urltest` group. Passing a `$prefix`
 * registers/reads a second copy of every option under prefixed flag names,
 * e.g. `configure($command, 'urltest')` adds `--urltestCountryCode`,
 * `--urltestExcludeOutbound`, `--urltestExceptOutbound`, etc., completely
 * independent from the unprefixed `--countryCode`, `--excludeOutbound`, ...
 *
 * ## Adding a new filter rule
 *
 * Add the option(s) in {@see self::configure()}, and the matching `if` block
 * in {@see self::resolve()} that pushes the new `*Criteria`. Nothing else in
 * the filtering pipeline (Specification/Factory/Registry/Service/UseCase)
 * needs to change - see `Filter/OutboundSpecificationFactoryRegistry.php`.
 */
final class OutboundFilterOptionsBinder
{
    public function configure(Command $command, string $prefix = ''): Command
    {
        return $command
            /**
             * 1. By country code (keep only), with its own except + own onlyAvailable flag
             */
            ->addOption(
                $this->option($prefix, 'countryCode'), null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Keep only outbounds whose country code matches one of the specified codes'
            )
            ->addOption(
                $this->option($prefix, 'countryCodeExcept'), null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Outbound tags that are never dropped by --' . $this->option($prefix, 'countryCode') . ' ' .
                '(they are still subject to every other filter)'
            )
            ->addOption(
                $this->option($prefix, 'countryOnlyAvailable'), null, InputOption::VALUE_NONE,
                'With --' . $this->option($prefix, 'countryCode') . ', also exclude outbounds whose country code could not be resolved'
            )
            /**
             * 2. Exclude by country code, with its own except + own onlyAvailable flag (independent of #1)
             */
            ->addOption(
                $this->option($prefix, 'excludeCountryCode'), null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Exclude outbounds whose country code matches one of the specified codes'
            )
            ->addOption(
                $this->option($prefix, 'excludeCountryCodeExcept'), null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Outbound tags that are never dropped by --' . $this->option($prefix, 'excludeCountryCode') . ' ' .
                '(they are still subject to every other filter)'
            )
            ->addOption(
                $this->option($prefix, 'excludeCountryOnlyAvailable'), null, InputOption::VALUE_NONE,
                'With --' . $this->option($prefix, 'excludeCountryCode') . ', also exclude outbounds whose country code could not be resolved'
            )
            /**
             * Shared country-code resolution setting for both #1 and #2 above
             */
            ->addOption(
                $this->option($prefix, 'countryOutboundIpFallback'), null, InputOption::VALUE_NONE,
                "Use the outbound IP specified in the configuration if its real IP could not be obtained"
            )
            /**
             * 4. By type (keep only), with its own except
             */
            ->addOption(
                $this->option($prefix, 'outboundType'), null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Keep only outbounds whose type matches one of the specified types'
            )
            ->addOption(
                $this->option($prefix, 'outboundTypeExcept'), null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Outbound tags that are never dropped by --' . $this->option($prefix, 'outboundType') . ' ' .
                '(they are still subject to every other filter)'
            )
            /**
             * 5. Exclude by type, with its own except
             */
            ->addOption(
                $this->option($prefix, 'excludeOutboundType'), null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Exclude outbounds whose type matches one of the specified types'
            )
            ->addOption(
                $this->option($prefix, 'excludeOutboundTypeExcept'), null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Outbound tags that are never dropped by --' . $this->option($prefix, 'excludeOutboundType') . ' ' .
                '(they are still subject to every other filter)'
            )
            /**
             * 6. By name
             */
            ->addOption(
                $this->option($prefix, 'excludeOutbound'), $prefix === '' ? 'e' : null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'One or more outbound tags to exclude by name'
            )
            /**
             * 7. Global bypass - skips every filter above entirely for the listed tags
             */
            ->addOption(
                $this->option($prefix, 'exceptOutbound'), null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'One or more outbound tags that will be ignored by all filters above and always kept'
            );
    }

    private function option(string $prefix, string $option): string
    {
        return $prefix === '' ? $option : $prefix . ucfirst($option);
    }

    public function resolve(InputInterface $input, string $prefix = ''): OutboundFilterCriteriaBag
    {
        $criteria = [];

        $excludeOutbound = $this->getOption($input, $prefix, 'excludeOutbound');
        if ($excludeOutbound) {
            $criteria[] = new TagExcludeCriteria(new Vector($excludeOutbound));
        }

        $excludeCountryCode = $this->getOption($input, $prefix, 'excludeCountryCode');
        if ($excludeCountryCode) {
            $excludeCountryCodeExcept = $this->getOption($input, $prefix, 'excludeCountryCodeExcept');

            $criteria[] = new CountryCodeExcludeCriteria(
                new Vector($excludeCountryCode),
                (bool)$this->getOption($input, $prefix, 'countryOutboundIpFallback'),
                (bool)$this->getOption($input, $prefix, 'excludeCountryOnlyAvailable'),
                $excludeCountryCodeExcept ? new Vector($excludeCountryCodeExcept) : null,
            );
        }

        $countryCode = $this->getOption($input, $prefix, 'countryCode');
        if ($countryCode) {
            $countryCodeExcept = $this->getOption($input, $prefix, 'countryCodeExcept');

            $criteria[] = new CountryCodeCriteria(
                new Vector($countryCode),
                (bool)$this->getOption($input, $prefix, 'countryOutboundIpFallback'),
                (bool)$this->getOption($input, $prefix, 'countryOnlyAvailable'),
                $countryCodeExcept ? new Vector($countryCodeExcept) : null,
            );
        }

        $excludeOutboundType = $this->getOption($input, $prefix, 'excludeOutboundType');
        if ($excludeOutboundType) {
            $excludeOutboundTypeExcept = $this->getOption($input, $prefix, 'excludeOutboundTypeExcept');

            $criteria[] = new TypeExcludeCriteria(
                new Vector($excludeOutboundType),
                $excludeOutboundTypeExcept ? new Vector($excludeOutboundTypeExcept) : null,
            );
        }

        $outboundType = $this->getOption($input, $prefix, 'outboundType');
        if ($outboundType) {
            $outboundTypeExcept = $this->getOption($input, $prefix, 'outboundTypeExcept');

            $criteria[] = new TypeCriteria(
                new Vector($outboundType),
                $outboundTypeExcept ? new Vector($outboundTypeExcept) : null,
            );
        }

        $exceptOutbound = $this->getOption($input, $prefix, 'exceptOutbound');

        return new OutboundFilterCriteriaBag(
            new Vector($criteria),
            $exceptOutbound ? new Vector($exceptOutbound) : null,
        );
    }

    private function getOption(InputInterface $input, string $prefix, string $option): mixed
    {
        return $input->getOption($this->option($prefix, $option));
    }
}
