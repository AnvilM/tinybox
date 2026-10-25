<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Application\Outbound\DTO\Override\OverrideOutboundDTO;
use App\Application\Outbound\DTO\UseCase\SelectOutbounds\SelectOutboundsDTO;
use App\Application\Outbound\DTO\UseCase\SetOutboundsDetour\SetOutboundsDetourDTO;
use App\Application\Outbound\Export\Specification\OutboundCoreSupportSpecification;
use App\Application\Outbound\Mapper\ToSchemeString\ToSchemeStringOutboundMapper;
use App\Application\Outbound\Override\OverrideOutboundService;
use App\Application\Outbound\Selection\Syntax\SelectorParser;
use App\Application\Outbound\UseCase\SelectOutbounds\SelectOutboundsUseCase;
use App\Application\Outbound\UseCase\SetOutboundsDetour\SetOutboundsDetourUseCase;
use App\Application\Shared\DTO\UseCase\CreateConfig\CreateConfigDTO;
use App\Application\Shared\DTO\UseCase\SaveConfig\SaveConfigDTO;
use App\Application\Shared\UseCase\CreateConfig\CreateConfigUseCase;
use App\Application\Shared\UseCase\SaveSingBoxConfig\SaveConfigUseCase;
use App\Application\Subscription\UseCase\GetSubscriptionWithName\GetSubscriptionWithNameUseCase;
use App\Commands\AbstractCommand;
use App\Commands\Shared\Options\CoreOptionsTrait;
use App\Commands\Shared\Options\DryRunOptionTrait;
use App\Commands\Shared\Options\OverridesOptionsTrait;
use App\Commands\Shared\Options\SelectionOptionsTrait;
use App\Domain\Outbound\Collection\UniqueTagOutboundsMap;
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
use Iva\Output\Verbosity;
use Psl\Collection\Vector;


final class ExportSubscriptionCommand extends AbstractCommand
{
    use SelectionOptionsTrait;
    use CoreOptionsTrait;
    use OverridesOptionsTrait;
    use DryRunOptionTrait;

    private const string URLTEST_FILTER_PREFIX = 'urltest';

    private Argument $nameArgument;
    private Option $urltestOption;
    private Option $detourOutboundOption;
    private Option $schemeOption;
    private Option $configOption;

    public function __construct(
        ReporterInstancePort                            $reporterInstancePort,
        private readonly GetSubscriptionWithNameUseCase $getSubscriptionWithNameUseCase,
        private readonly SelectOutboundsUseCase         $selectOutboundsUseCase,
        private readonly SelectorParser                 $selectorParser,
        private readonly SetOutboundsDetourUseCase      $setOutboundsDetourUseCase,
        private readonly CreateConfigUseCase            $createConfigUseCase,
        private readonly SaveConfigUseCase              $saveSingBoxConfigUseCase,
        private readonly OverrideOutboundService        $overrideOutboundService,
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

        $this->configureSelectionOptions(includeUrltestVariant: true, urltestPrefix: self::URLTEST_FILTER_PREFIX);
        $this->configureCoreOptions();
        $this->configureOverridesOptions();
        $this->configureDryRunOption();
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
         * Select outbounds (main config group)
         */
        $exportAsScheme = $input->flag($this->schemeOption);

        $constraints = $exportAsScheme
            ? new Vector([])
            : new Vector([new OutboundCoreSupportSpecification($this->resolveConfigType($input)->toCoreType())]);

        $subscriptionOutbounds = $this->selectOutboundsUseCase->handle(new SelectOutboundsDTO(
            $subscriptionOutbounds,
            selector: $this->resolveSelector($input, $this->selectorParser),
            constraints: $constraints,
        ));


        if ($subscriptionOutbounds->isEmpty()) throw new CriticalException("No outbound calls matching the selection were found");


        /**
         * Overrides
         */
        $subscriptionOutbounds = $this->overrideOutboundService->override(
            $subscriptionOutbounds, $this->resolveOverrideTypes($input)
        );


        /**
         * Urltest selection
         */

        if (!$exportAsScheme) {
            $urltestOutbounds = null;
            if ($input->flag($this->urltestOption)) {
                $urltestOutbounds = clone $subscriptionOutbounds;

                $urltestSelector = $this->resolveSelector($input, $this->selectorParser, self::URLTEST_FILTER_PREFIX);

                if ($urltestSelector !== null) {
                    $urltestOutbounds = $this->selectOutboundsUseCase->handle(new SelectOutboundsDTO(
                        $urltestOutbounds,
                        selector: $urltestSelector,
                    ));
                }
            }

            /**
             * Set detour outbound
             */
            $detourOutbound = $input->option($this->detourOutboundOption);


            if ($detourOutbound !== null) try {
                $subscriptionOutbounds = $this->setOutboundsDetourUseCase->handle(
                    new SetOutboundsDetourDTO(
                        $subscriptionOutbounds,
                        new UniqueTagOutboundsMap($subscriptionOutbounds->getOutbounds())->getWithTag($detourOutbound)
                    )
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
        if (!$this->isDryRun($input))
            $this->saveSingBoxConfigUseCase->handle(new SaveConfigDTO($singBoxConfigJSON));
        else $output->writeln($singBoxConfigJSON, Verbosity::Quiet);

        return ExitCode::Ok->value;
    }
}
