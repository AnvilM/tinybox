<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Application\Outbound\DTO\UseCase\FilterOutbounds\FilterOutboundsDTO;
use App\Application\Outbound\DTO\UseCase\OverrideOutbounds\OverrideOutboundDTO;
use App\Application\Outbound\DTO\UseCase\SetOutboundsDetour\SetOutboundsDetourDTO;
use App\Application\Outbound\Filter\Criteria\OutboundCoreSupportCriteria;
use App\Application\Outbound\Mapper\ToSchemeString\ToSchemeStringOutboundMapper;
use App\Application\Outbound\UseCase\FilterOutbounds\FilterOutboundsUseCase;
use App\Application\Outbound\UseCase\OverrideOutbounds\OverrideOutboundsUseCase;
use App\Application\Outbound\UseCase\SetOutboundsDetour\SetOutboundsDetourUseCase;
use App\Application\Shared\DTO\UseCase\CreateConfig\CreateConfigDTO;
use App\Application\Shared\DTO\UseCase\SaveConfig\SaveConfigDTO;
use App\Application\Shared\UseCase\CreateConfig\CreateConfigUseCase;
use App\Application\Shared\UseCase\SaveSingBoxConfig\SaveConfigUseCase;
use App\Application\Subscription\UseCase\GetSubscriptionWithName\GetSubscriptionWithNameUseCase;
use App\Commands\AbstractCommand;
use App\Commands\Shared\Options\CoreOptionsTrait;
use App\Commands\Shared\Options\OutboundFilterOptionsTrait;
use App\Commands\Shared\Options\OverridesOptionsTrait;
use App\Domain\Outbound\Exception\OutboundNotFoundException;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterInstancePort;
use App\Domain\Subscription\Entity\ConfigSubscription;
use App\Domain\Subscription\Entity\OutboundsSubscription;
use Iva\ExitCode;
use Iva\Input\Argument;
use Iva\Input\Input;
use Iva\Input\Option;
use Iva\Output\Output;
use Psl\Collection\MutableVector;

/**
 * Ported from Symfony to Iva. Behaviour is unchanged; only how the option
 * blocks are wired up is different — see Shared/Options/*Trait.php for why
 * `use`-ing a trait replaced `optionGroups()` + `$this->optionGroups->get(...)`.
 */
final class ExportSubscriptionCommand extends AbstractCommand
{
    use OutboundFilterOptionsTrait;
    use CoreOptionsTrait;
    use OverridesOptionsTrait;

    private const string URLTEST_FILTER_PREFIX = 'urltest';

    private Argument $nameArgument;
    private Option $urltestOption;
    private Option $detourOutboundOption;
    private Option $schemeOption;
    private Option $configOption;

    public function __construct(
        ReporterInstancePort                            $reporterInstancePort,
        private readonly GetSubscriptionWithNameUseCase $getSubscriptionWithNameUseCase,
        private readonly FilterOutboundsUseCase         $filterOutboundsUseCase,
        private readonly SetOutboundsDetourUseCase      $setOutboundsDetourUseCase,
        private readonly CreateConfigUseCase            $createConfigUseCase,
        private readonly SaveConfigUseCase              $saveSingBoxConfigUseCase,
        private readonly OverrideOutboundsUseCase       $overrideOutboundsUseCase,
        private readonly ToSchemeStringOutboundMapper   $toSchemeStringOutboundMapper,
        ConfigInstancePort                              $configInstancePort,
    )
    {
        parent::__construct($reporterInstancePort, $configInstancePort);
    }

    protected function configureCommand(): void
    {
        $this->setName('export');
        $this->setDescription('Export subscription outbounds');

        $this->nameArgument = $this->addArgument(Argument::string('name', 'Subscription name'));
        $this->urltestOption = $this->addOption(Option::flag('urltest', 'u', 'Add urltest outbound to config'));
        $this->detourOutboundOption = $this->addOption(Option::string(
            name: 'detourOutbound',
            description: 'Use the specified outbound as detour for all other outbounds',
            valueRequired: false,
        ));
        $this->schemeOption = $this->addOption(Option::flag('scheme', description: 'Export subscription outbounds as schemes'));
        // Registered but unused in handle(), same as in the previous version.
        $this->configOption = $this->addOption(Option::flag('config', description: 'Export subscription outbounds as config'));

        $this->configureOutboundFilterOptions(includeUrltestVariant: true, urltestPrefix: self::URLTEST_FILTER_PREFIX);
        $this->configureCoreOptions();
        $this->configureOverridesOptions();
    }

    protected function handle(Input $input, Output $output): int
    {
        $subscription = $this->getSubscriptionWithNameUseCase->handle(
            $input->argument($this->nameArgument)
        );

        if ($subscription instanceof ConfigSubscription) {
            $this->saveSingBoxConfigUseCase->handle(new SaveConfigDTO($subscription->getConfigString()));

            return ExitCode::Ok->value;
        }

        if (!($subscription instanceof OutboundsSubscription)) return ExitCode::GeneralError->value;

        if ($subscription->getOutbounds()->isEmpty()) throw new CriticalException("Not found outbounds for subscription");

        $subscriptionOutbounds = $subscription->getOutbounds();

        /**
         * Filter outbounds (main config group)
         */
        $mainFilters = $this->resolveOutboundFilterCriteria($input);

        $exportAsScheme = $input->flag($this->schemeOption);

        $criteria = $exportAsScheme ? $mainFilters->criteria : new MutableVector($mainFilters->criteria->toArray())
            ->add(new OutboundCoreSupportCriteria(
                $this->resolveConfigType($input)->toCoreType()
            ));

        $subscriptionOutbounds = $this->filterOutboundsUseCase->handle(new FilterOutboundsDTO(
            $subscriptionOutbounds,
            criteria: $criteria,
            ignoreOutbounds: $mainFilters->ignoreOutbounds,
        ));


        $subscriptionOutbounds = $this->overrideOutboundsUseCase->override(
            new OverrideOutboundDTO(
                $subscriptionOutbounds,
                $this->resolveOverrideUUID($input),
                $this->resolveOverrideSSPass($input),
            )
        );

        if ($subscriptionOutbounds->isEmpty()) throw new CriticalException("No outbound calls matching the filter criteria were found");

        if (!$exportAsScheme) {
            $urltestOutbounds = null;
            if ($input->flag($this->urltestOption)) {
                $urltestOutbounds = clone $subscriptionOutbounds;

                $urltestFilters = $this->resolveOutboundFilterCriteria($input, self::URLTEST_FILTER_PREFIX);

                if (!$urltestFilters->isEmpty()) {
                    $urltestOutbounds = $this->filterOutboundsUseCase->handle(new FilterOutboundsDTO(
                        $urltestOutbounds,
                        criteria: $urltestFilters->criteria,
                        ignoreOutbounds: $urltestFilters->ignoreOutbounds,
                    ));
                }
            }

            /**
             * Set detour outbound
             */
            $detourOutbound = $input->option($this->detourOutboundOption);

            if ($detourOutbound !== null) try {
                $subscriptionOutbounds = $this->setOutboundsDetourUseCase->handle(
                    new SetOutboundsDetourDTO($subscriptionOutbounds, $subscriptionOutbounds->getWithTag($detourOutbound))
                );
            } catch (OutboundNotFoundException) {
                throw new CriticalException("Outbound with tag '{$detourOutbound}' not found");
            }


            $singBoxConfigJSON = $this->createConfigUseCase->handle(
                new CreateConfigDTO(
                    $subscriptionOutbounds,
                    $this->resolveConfigType($input),
                    $urltestOutbounds
                )
            );
        } else {
            $singBoxConfigJSON = "";
            foreach ($subscriptionOutbounds->getOutbounds() as $outbound) {
                $singBoxConfigJSON .= $this->toSchemeStringOutboundMapper->map($outbound) . "\n";
            }
        }

        $this->saveSingBoxConfigUseCase->handle(new SaveConfigDTO($singBoxConfigJSON));

        return ExitCode::Ok->value;
    }
}
