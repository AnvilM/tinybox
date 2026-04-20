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
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
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
    )
    {
        parent::__construct($reporterPort);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $subscription = $this->getSubscriptionWithNameUseCase->handle(
            $input->getArgument('name')
        );

        if ($subscription->getOutbounds()->isEmpty()) throw new CriticalException("Not found schemes for subscription");


        $subscriptionOutbounds = $subscription->getOutbounds();

        if ($input->getOption('countryCode'))
            $filterCountryCodesDTO = new FilterCountryCodesDTO(
                new Vector($input->getOption('countryCode')),
                $input->getOption('exceptCountryCode') ? new Vector($input->getOption('exceptCountryCode')) : null,
                $input->getOption('countryCodeForce')
            );

        if ($input->getOption('excludeCountryCode'))
            $filterExcludeCountryCodesDTO = new FilterExcludeCountryCodesDTO(
                new Vector($input->getOption('excludeCountryCode')),
                $input->getOption('exceptExcludeCountryCode') ? new Vector($input->getOption('exceptExcludeCountryCode')) : null,
                $input->getOption('excludeCountryCodeForce')
            );

        if (isset($filterCountryCodesDTO) || isset($filterExcludeCountryCodesDTO))
            $subscriptionOutbounds = $this->filterOutboundsUseCase->handle(new FilterOutboundsDTO(
                $subscriptionOutbounds, null, $filterExcludeCountryCodesDTO ?? null, $filterCountryCodesDTO ?? null
            ));


        if ($input->getOption('detour')) try {
            $subscriptionOutbounds = $this->setOutboundsDetourUseCase->handle(new SetOutboundsDetourDTO(
                $subscriptionOutbounds, $subscriptionOutbounds->getWithId($input->getOption('detour'))
            ));
        } catch (OutboundNotFoundException) {
            throw new CriticalException("Outbound with tag '{$input->getOption('detour')}' not found. Try to remove filters");
        }


        $outboundsLatency = $this->outboundsLatencyUseCase->handle(new OutboundsLatencyDTO(
            $subscriptionOutbounds, $input->getArgument('method')
        ));


        $table = [];
        foreach ($outboundsLatency as $ol) {
            $table[] = [
                'type' => $ol->outbound->getType()->value,
                'tag' => $ol->outbound->getTagString(),
                'latency' => $ol->latency ?? 'N/A'
            ];
        }

        new CLImate()->table($table);


        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Subscription name')
            ->addArgument('method', InputArgument::OPTIONAL, 'Test method e.g. proxy_get or tcp_ping. If not provided, will be used method form config')
            ->addOption('countryCode', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY)
            ->addOption('exceptCountryCode', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY)
            ->addOption('countryCodeForce', null, InputOption::VALUE_NONE)
            ->addOption('excludeCountryCode', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY)
            ->addOption('exceptExcludeCountryCode', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY)
            ->addOption('excludeCountryCodeForce', null, InputOption::VALUE_NONE)
            ->addOption('detour', null, InputOption::VALUE_OPTIONAL)
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug messages');
    }
}