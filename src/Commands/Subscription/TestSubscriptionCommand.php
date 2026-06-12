<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Application\Shared\DTO\UseCase\FilterOutbounds\FilterCountryCodesDTO;
use App\Application\Shared\DTO\UseCase\FilterOutbounds\FilterExcludeCountryCodesDTO;
use App\Application\Shared\DTO\UseCase\FilterOutbounds\FilterOutboundsDTO;
use App\Application\Shared\DTO\UseCase\OutboundsLatency\OutboundsLatencyDTO;
use App\Application\Shared\DTO\UseCase\SetOutboundsDetour\SetOutboundsDetourDTO;
use App\Application\Shared\UseCase\FilterOutbounds\FilterOutboundsUseCase;
use App\Application\Shared\UseCase\OutboundsLatency\OutboundsLatencyUseCase;
use App\Application\Shared\UseCase\SetOutboundsDetour\SetOutboundsDetourUseCase;
use App\Application\Subscription\UseCase\GetSubscriptionWithName\GetSubscriptionWithNameUseCase;
use App\Commands\AbstractCommand;
use App\Domain\Outbound\Exception\OutboundNotFoundException;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use App\Domain\Subscription\Entity\ConfigSubscription;
use App\Domain\Subscription\Entity\OutboundsSubscription;
use League\CLImate\CLImate;
use Psl\Collection\Vector;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'subscription:test', description: 'Test subscription outbounds', aliases: ['sub:test'])]
final class TestSubscriptionCommand extends AbstractCommand
{
    public function __construct(
        ReporterPort                                    $reporterPort,
        private readonly GetSubscriptionWithNameUseCase $getSubscriptionWithNameUseCase,
        private readonly OutboundsLatencyUseCase        $outboundsLatencyUseCase,
        private readonly FilterOutboundsUseCase         $filterOutboundsUseCase,
        private readonly SetOutboundsDetourUseCase      $setOutboundsDetourUseCase,
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

        if ($input->getOption('countryCode'))
            $filterCountryCodesDTO = new FilterCountryCodesDTO(
                new Vector($input->getOption('countryCode')),
                $input->getOption('exceptOutbound') ? new Vector($input->getOption('exceptOutbound')) : null,
                $input->getOption('countryOutboundIpFallback'),
                $input->getOption('countryOnlyAvailable')
            );

        if ($input->getOption('excludeCountryCode'))
            $filterExcludeCountryCodesDTO = new FilterExcludeCountryCodesDTO(
                new Vector($input->getOption('excludeCountryCode')),
                $input->getOption('exceptOutbound') ? new Vector($input->getOption('exceptOutbound')) : null,
                $input->getOption('countryOutboundIpFallback'),
                $input->getOption('countryOnlyAvailable')
            );

        if (isset($filterCountryCodesDTO) || isset($filterExcludeCountryCodesDTO))
            $subscriptionOutbounds = $this->filterOutboundsUseCase->handle(new FilterOutboundsDTO(
                $subscriptionOutbounds, null, $filterExcludeCountryCodesDTO ?? null, $filterCountryCodesDTO ?? null
            ));


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
            ->addOption('countryCode', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Filter outbounds and use only those whose country code match the specified one')
            ->addOption('excludeCountryCode', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Filter outbounds and use only those whose country code does not match the specified one')
            ->addOption('exceptOutbound', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, "One or more outbounds that will be ignored by all filters")
            ->addOption('countryOutboundIpFallback', null, InputOption::VALUE_NONE, "Use the outbound IP specified in the configuration if its real IP could not be obtained")
            ->addOption('detourOutbound', null, InputOption::VALUE_OPTIONAL, "Use the specified outbound as detour for all other outbounds")
            ->addOption('countryOnlyAvailable', null, InputOption::VALUE_NONE, "Exclude all outbounds for which the country code could not be obtained");
    }
}