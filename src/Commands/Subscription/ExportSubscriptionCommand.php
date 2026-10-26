<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Application\Outbound\Detour\DetourSpecParser;
use App\Application\Outbound\DTO\UseCase\RunOutboundPipeline\RunOutboundPipelineDTO;
use App\Application\Outbound\DTO\UseCase\SelectOutbounds\SelectOutboundsDTO;
use App\Application\Outbound\Export\Specification\OutboundCoreSupportSpecification;
use App\Application\Outbound\Mapper\ToSchemeString\ToSchemeStringOutboundMapper;
use App\Application\Outbound\Override\OverrideSpecParser;
use App\Application\Outbound\Selection\SelectorComposer;
use App\Application\Outbound\Selection\Syntax\SelectorParser;
use App\Application\Outbound\Urltest\UrltestGroupSpecParser;
use App\Application\Outbound\UseCase\RunOutboundPipeline\RunOutboundPipelineUseCase;
use App\Application\Outbound\UseCase\SelectOutbounds\SelectOutboundsUseCase;
use App\Application\Shared\DTO\UseCase\CreateConfig\CreateConfigDTO;
use App\Application\Shared\DTO\UseCase\SaveConfig\SaveConfigDTO;
use App\Application\Shared\UseCase\CreateConfig\CreateConfigUseCase;
use App\Application\Shared\UseCase\SaveSingBoxConfig\SaveConfigUseCase;
use App\Application\Subscription\UseCase\GetSubscriptionWithName\GetSubscriptionWithNameUseCase;
use App\Commands\AbstractCommand;
use App\Commands\Shared\Options\CoreOptionsTrait;
use App\Commands\Shared\Options\DryRunOptionTrait;
use App\Commands\Shared\Options\GlobalSelectionOptionsTrait;
use App\Commands\Shared\Options\Pipeline\PipelineFlagOrder;
use App\Commands\Shared\Options\PipelineOptionsTrait;
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
    use DryRunOptionTrait;
    use PipelineOptionsTrait;

    private Argument $nameArgument;
    private Option $schemeOption;
    private Option $configOption;

    public function __construct(
        ReporterInstancePort                            $reporterInstancePort,
        private readonly GetSubscriptionWithNameUseCase $getSubscriptionWithNameUseCase,
        private readonly SelectOutboundsUseCase         $selectOutboundsUseCase,
        private readonly SelectorParser                 $selectorParser,
        private readonly SelectorComposer               $selectorComposer,
        private readonly OverrideSpecParser             $overrideSpecParser,
        private readonly UrltestGroupSpecParser         $urltestGroupSpecParser,
        private readonly DetourSpecParser               $detourSpecParser,
        private readonly PipelineFlagOrder              $pipelineFlagOrder,
        private readonly RunOutboundPipelineUseCase     $runOutboundPipelineUseCase,
        private readonly CreateConfigUseCase            $createConfigUseCase,
        private readonly SaveConfigUseCase              $saveSingBoxConfigUseCase,
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
        $this->configurePipelineOptions();
        $this->configureCoreOptions();
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

        $exportAsScheme = $input->flag($this->schemeOption);


        /**
         * Parse every flag first: syntax errors must appear before any selection, DNS or file work
         */
        $steps = $this->resolvePipelineSteps(
            $input, $this->pipelineFlagOrder, $this->overrideSpecParser, $this->urltestGroupSpecParser, $this->detourSpecParser,
            overridesOnly: $exportAsScheme,
        );


        /**
         * Select outbounds (main config group)
         */

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
         * Ordered pipeline: override / urltest / detour in the order of flags
         */
        $outbounds = $steps->isEmpty()
            ? $subscriptionOutbounds
            : $this->runOutboundPipelineUseCase->handle(new RunOutboundPipelineDTO(
                $subscriptionOutbounds, $steps, $this->resolveConfigType($input)->toCoreType()
            ));


        if (!$exportAsScheme) {
            $singBoxConfigJSON = $this->createConfigUseCase->handle(
                new CreateConfigDTO($outbounds, $this->resolveConfigType($input))
            );
        } else {
            $singBoxConfigJSON = "";
            foreach ($outbounds->getOutbounds() as $outbound) {
                $singBoxConfigJSON .= $this->toSchemeStringOutboundMapper->map($outbound) . "\n";
            }
        }
        if (!$this->isDryRun($input))
            $this->saveSingBoxConfigUseCase->handle(new SaveConfigDTO($singBoxConfigJSON));
        else $output->writeln($singBoxConfigJSON, Verbosity::Quiet);

        return ExitCode::Ok->value;
    }
}
