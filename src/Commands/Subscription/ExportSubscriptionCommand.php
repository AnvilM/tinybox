<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Application\Outbound\Detour\DetourSpecParser;
use App\Application\Outbound\DTO\UseCase\ApplyDetours\ApplyDetoursDTO;
use App\Application\Outbound\DTO\UseCase\BuildUrltestOutbounds\BuildUrltestOutboundsDTO;
use App\Application\Outbound\DTO\UseCase\SelectOutbounds\SelectOutboundsDTO;
use App\Application\Outbound\Export\Specification\OutboundCoreSupportSpecification;
use App\Application\Outbound\Mapper\ToSchemeString\ToSchemeStringOutboundMapper;
use App\Application\Outbound\Override\OverrideOutboundService;
use App\Application\Outbound\Selection\SelectorComposer;
use App\Application\Outbound\Selection\Syntax\SelectorParser;
use App\Application\Outbound\Urltest\UrltestGroupSpecParser;
use App\Application\Outbound\UseCase\ApplyDetours\ApplyDetoursUseCase;
use App\Application\Outbound\UseCase\BuildUrltestOutbounds\BuildUrltestOutboundsUseCase;
use App\Application\Outbound\UseCase\SelectOutbounds\SelectOutboundsUseCase;
use App\Application\Shared\DTO\UseCase\CreateConfig\CreateConfigDTO;
use App\Application\Shared\DTO\UseCase\SaveConfig\SaveConfigDTO;
use App\Application\Shared\UseCase\CreateConfig\CreateConfigUseCase;
use App\Application\Shared\UseCase\SaveSingBoxConfig\SaveConfigUseCase;
use App\Application\Subscription\UseCase\GetSubscriptionWithName\GetSubscriptionWithNameUseCase;
use App\Commands\AbstractCommand;
use App\Commands\Shared\Options\CoreOptionsTrait;
use App\Commands\Shared\Options\DetourOptionsTrait;
use App\Commands\Shared\Options\DryRunOptionTrait;
use App\Commands\Shared\Options\GlobalSelectionOptionsTrait;
use App\Commands\Shared\Options\OverridesOptionsTrait;
use App\Commands\Shared\Options\UrltestOptionsTrait;
use App\Domain\Outbound\Collection\OutboundMap;
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
    use GlobalSelectionOptionsTrait;
    use CoreOptionsTrait;
    use OverridesOptionsTrait;
    use DryRunOptionTrait;
    use UrltestOptionsTrait;
    use DetourOptionsTrait;

    private Argument $nameArgument;
    private Option $schemeOption;
    private Option $configOption;

    public function __construct(
        ReporterInstancePort                            $reporterInstancePort,
        private readonly GetSubscriptionWithNameUseCase $getSubscriptionWithNameUseCase,
        private readonly SelectOutboundsUseCase         $selectOutboundsUseCase,
        private readonly SelectorParser                 $selectorParser,
        private readonly SelectorComposer               $selectorComposer,
        private readonly UrltestGroupSpecParser         $urltestGroupSpecParser,
        private readonly BuildUrltestOutboundsUseCase   $buildUrltestOutboundsUseCase,
        private readonly DetourSpecParser               $detourSpecParser,
        private readonly ApplyDetoursUseCase            $applyDetoursUseCase,
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
        $this->schemeOption = $this->addOption(Option::flag('scheme', description: 'Export subscription outbounds as schemes'));
        // Registered but unused in handle(), same as in the previous version.
        $this->configOption = $this->addOption(Option::flag('config', description: 'Export subscription outbounds as config'));

        $this->configureGlobalSelectionOptions();
        $this->configureUrltestOptions();
        $this->configureDetourOptions();
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
            selector: $this->resolveGlobalSelector($input, $this->selectorParser, $this->selectorComposer),
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
            $urltestOutbounds = new OutboundMap();

            $definitions = $this->resolveUrltestDefinitions($input, $this->urltestGroupSpecParser);

            if (!$definitions->isEmpty()) {
                $urltestOutbounds = $this->buildUrltestOutboundsUseCase->handle(
                    new BuildUrltestOutboundsDTO($subscriptionOutbounds, $definitions, $this->resolveConfigType($input)->toCoreType())
                );
            }

            /**
             * Regular outbounds + urltest outbounds
             */
            $configOutbounds = (clone $subscriptionOutbounds)->merge($urltestOutbounds);


            /**
             * Detours
             */
            $detourDefinitions = $this->resolveDetourDefinitions($input, $this->detourSpecParser);

            if (!$detourDefinitions->isEmpty()) {
                $this->applyDetoursUseCase->handle(
                    new ApplyDetoursDTO($configOutbounds, $detourDefinitions, $this->resolveConfigType($input)->toCoreType())
                );
            }


            $singBoxConfigJSON = $this->createConfigUseCase->handle(
                new CreateConfigDTO($configOutbounds, $this->resolveConfigType($input))
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
