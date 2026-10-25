<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Application\Outbound\DTO\UseCase\OutboundsLatency\OutboundsLatencyDTO;
use App\Application\Outbound\DTO\UseCase\SelectOutbounds\SelectOutboundsDTO;
use App\Application\Outbound\DTO\UseCase\SetOutboundsDetour\SetOutboundsDetourDTO;
use App\Application\Outbound\Export\Specification\OutboundCoreSupportSpecification;
use App\Application\Outbound\Selection\Syntax\SelectorParser;
use App\Application\Outbound\UseCase\OutboundsLatency\OutboundsLatencyUseCase;
use App\Application\Outbound\UseCase\SelectOutbounds\SelectOutboundsUseCase;
use App\Application\Outbound\UseCase\SetOutboundsDetour\SetOutboundsDetourUseCase;
use App\Application\Subscription\UseCase\GetSubscriptionWithName\GetSubscriptionWithNameUseCase;
use App\Commands\AbstractCommand;
use App\Commands\Shared\Options\SelectionOptionsTrait;
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
use Psl\Collection\Vector;


final class TestSubscriptionCommand extends AbstractCommand
{
    use SelectionOptionsTrait;

    private Argument $nameArgument;
    private Argument $methodArgument;
    private Option $detourOutboundOption;

    public function __construct(
        ReporterInstancePort                            $reporterInstancePort,
        private readonly GetSubscriptionWithNameUseCase $getSubscriptionWithNameUseCase,
        private readonly OutboundsLatencyUseCase        $outboundsLatencyUseCase,
        private readonly SelectOutboundsUseCase         $selectOutboundsUseCase,
        private readonly SelectorParser                 $selectorParser,
        private readonly SetOutboundsDetourUseCase      $setOutboundsDetourUseCase,
        ConfigInstancePort                              $configInstancePort,
    )
    {
        parent::__construct($reporterInstancePort, $configInstancePort);
    }

    protected function configureCommand(): void
    {
        $this->setName('test');
        $this->setDescription('Test subscription outbounds. NOTE: Only for sing box outbounds');

        $this->nameArgument = $this->addArgument(Argument::string('name', 'Subscription name'));
        $this->methodArgument = $this->addArgument(Argument::string(
            name: 'method',
            description: 'Test method e.g. proxy_get or tcp_ping. If not provided, will be used method form config',
            optional: true,
        ));
        $this->detourOutboundOption = $this->addOption(Option::string(
            name: 'detourOutbound',
            description: 'Use the specified outbound as detour for all other outbounds',
            valueRequired: false,
        ));

        $this->configureSelectionOptions();
    }

    protected function handle(Input $input, Output $output): int
    {
        $subscription = $this->getSubscriptionWithNameUseCase->handle(
            $input->argument($this->nameArgument)
        );

        if ($subscription instanceof ConfigSubscription) {
            throw new CriticalException("Subscription test is not available for config subscriptions");
        }

        if (!($subscription instanceof OutboundsSubscription)) return ExitCode::GeneralError->value;

        if ($subscription->getOutbounds()->isEmpty()) throw new CriticalException("Not found schemes for subscription");


        $subscriptionOutbounds = $subscription->getOutbounds();


        /**
         * Select outbounds, keeping only the sing-box compatible ones
         */
        $selector = $this->resolveSelector($input, $this->selectorParser);

        $subscriptionOutbounds = $this->selectOutboundsUseCase->handle(new SelectOutboundsDTO(
            $subscriptionOutbounds,
            selector: $selector,
            constraints: new Vector([new OutboundCoreSupportSpecification(CoreType::SingBox)]),
        ));


        /**
         * Set detour outbound
         */
        $detourOutbound = $input->option($this->detourOutboundOption);

        if ($detourOutbound !== null) try {
            $subscriptionOutbounds = $this->setOutboundsDetourUseCase->handle(new SetOutboundsDetourDTO(
                $subscriptionOutbounds, $subscriptionOutbounds->getWithTag($detourOutbound)
            ));
        } catch (OutboundNotFoundException) {
            throw new CriticalException("Outbound with tag '{$detourOutbound}' not found. Try to remove selection flags");
        }


        $outboundsLatency = $this->outboundsLatencyUseCase->handle(new OutboundsLatencyDTO(
            $subscriptionOutbounds, $input->argument($this->methodArgument)
        ));


        $table = $output->table()->headers(['type', 'tag', 'latency', 'ip']);

        foreach ($outboundsLatency as $ol) {
            $table->row([
                $ol->outbound->getType()->value,
                $ol->outbound->getTagString(),
                (string)($ol->latency ?? 'N/A'),
                $ol->outbound->getServerString(),
            ]);
        }

        $table->render();


        return ExitCode::Ok->value;
    }
}
