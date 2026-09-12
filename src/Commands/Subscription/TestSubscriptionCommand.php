<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Application\Outbound\DTO\UseCase\FilterOutbounds\FilterOutboundsDTO;
use App\Application\Outbound\DTO\UseCase\OutboundsLatency\OutboundsLatencyDTO;
use App\Application\Outbound\DTO\UseCase\SetOutboundsDetour\SetOutboundsDetourDTO;
use App\Application\Outbound\Export\CoreType;
use App\Application\Outbound\Filter\Criteria\OutboundCoreSupportCriteria;
use App\Application\Outbound\UseCase\FilterOutbounds\FilterOutboundsUseCase;
use App\Application\Outbound\UseCase\OutboundsLatency\OutboundsLatencyUseCase;
use App\Application\Outbound\UseCase\SetOutboundsDetour\SetOutboundsDetourUseCase;
use App\Application\Subscription\UseCase\GetSubscriptionWithName\GetSubscriptionWithNameUseCase;
use App\Commands\AbstractCommand;
use App\Commands\Shared\OutboundFilter\OutboundFilterOptionsBinder;
use App\Domain\Outbound\Exception\OutboundNotFoundException;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use App\Domain\Subscription\Entity\ConfigSubscription;
use App\Domain\Subscription\Entity\OutboundsSubscription;
use League\CLImate\CLImate;
use Psl\Collection\MutableVector;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'subscription:test', description: 'Test subscription outbounds. NOTE: Only for sing box outbounds', aliases: ['sub:test'])]
final class TestSubscriptionCommand extends AbstractCommand
{
    public function __construct(
        ReporterPort                                    $reporterPort,
        private readonly GetSubscriptionWithNameUseCase $getSubscriptionWithNameUseCase,
        private readonly OutboundsLatencyUseCase        $outboundsLatencyUseCase,
        private readonly FilterOutboundsUseCase         $filterOutboundsUseCase,
        private readonly SetOutboundsDetourUseCase      $setOutboundsDetourUseCase,
        private readonly OutboundFilterOptionsBinder    $outboundFilterOptionsBinder,
        ConfigInstancePort                              $configInstancePort,
    )
    {
        parent::__construct($reporterPort, $configInstancePort);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $subscription = $this->getSubscriptionWithNameUseCase->handle(
            $input->getArgument('name')
        );

        if ($subscription instanceof ConfigSubscription) {
            throw new CriticalException("Subscription test is not available for config subscriptions");
        }

        if (!($subscription instanceof OutboundsSubscription)) return self::FAILURE;

        if ($subscription->getOutbounds()->isEmpty()) throw new CriticalException("Not found schemes for subscription");


        $subscriptionOutbounds = $subscription->getOutbounds();


        /**
         * Filter outbounds
         */
        $filters = $this->outboundFilterOptionsBinder->resolve($input);

        /**
         * Filter non sing-box outbounds
         */
        $criteria = new MutableVector($filters->criteria->toArray())
            ->add(new OutboundCoreSupportCriteria(CoreType::SingBox));

        $subscriptionOutbounds = $this->filterOutboundsUseCase->handle(new FilterOutboundsDTO(
            $subscriptionOutbounds,
            criteria: $criteria,
            ignoreOutbounds: $filters->ignoreOutbounds,
        ));


        /**
         * Set detour outbound
         */
        if ($input->getOption('detourOutbound')) try {
            $subscriptionOutbounds = $this->setOutboundsDetourUseCase->handle(new SetOutboundsDetourDTO(
                $subscriptionOutbounds, $subscriptionOutbounds->getWithTag($input->getOption('detourOutbound'))
            ));
        } catch (OutboundNotFoundException) {
            throw new CriticalException("Outbound with tag '{$input->getOption('detourOutbound')}' not found. Try to remove filters");
        }


        $outboundsLatency = $this->outboundsLatencyUseCase->handle(new OutboundsLatencyDTO(
            $subscriptionOutbounds, $input->getArgument('method')
        ));


        $table = [];
        foreach ($outboundsLatency as $ol) {
            $table[] = [
                'type' => $ol->outbound->getType()->value,
                'tag' => $ol->outbound->getTagString(),
                'latency' => $ol->latency ?? 'N/A',
                'ip' => $ol->outbound->getServer()
            ];
        }

        new CLImate()->table($table);


        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Subscription name')
            ->addArgument('method', InputArgument::OPTIONAL, 'Test method e.g. proxy_get or tcp_ping. If not provided, will be used method form config')
            ->addOption('detourOutbound', null, InputOption::VALUE_OPTIONAL, "Use the specified outbound as detour for all other outbounds");

        /**
         * Registers the entire filtering option set (see
         * OutboundFilterOptionsBinder), identical to what
         * ApplySubscriptionCommand uses for its main config group.
         */
        $this->outboundFilterOptionsBinder->configure($this);
    }
}
