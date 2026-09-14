<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Application\Outbound\DTO\UseCase\FilterOutbounds\FilterOutboundsDTO;
use App\Application\Outbound\DTO\UseCase\SetOutboundsDetour\SetOutboundsDetourDTO;
use App\Application\Outbound\UseCase\FilterOutbounds\FilterOutboundsUseCase;
use App\Application\Outbound\UseCase\SetOutboundsDetour\SetOutboundsDetourUseCase;
use App\Application\Shared\DTO\UseCase\CreateConfig\CreateConfigDTO;
use App\Application\Shared\DTO\UseCase\SaveConfig\SaveConfigDTO;
use App\Application\Shared\UseCase\CreateConfig\CreateConfigUseCase;
use App\Application\Shared\UseCase\SaveSingBoxConfig\SaveConfigUseCase;
use App\Application\Subscription\UseCase\GetSubscriptionWithName\GetSubscriptionWithNameUseCase;
use App\Commands\AbstractCommand;
use App\Commands\Shared\OptionGroup\Groups\CoreOptionsGroup;
use App\Commands\Shared\OptionGroup\Groups\OutboundFilterOptionsGroup;
use App\Domain\Outbound\Exception\OutboundNotFoundException;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use App\Domain\Subscription\Entity\ConfigSubscription;
use App\Domain\Subscription\Entity\OutboundsSubscription;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'subscription:apply', description: 'Apply subscription', aliases: ['sub:apply'])]
final class ApplySubscriptionCommand extends AbstractCommand
{

    private const string URLTEST_FILTER_PREFIX = 'urltest';

    public function __construct(
        ReporterPort                                    $reporterPort,
        private readonly GetSubscriptionWithNameUseCase $getSubscriptionWithNameUseCase,
        private readonly FilterOutboundsUseCase         $filterOutboundsUseCase,
        private readonly SetOutboundsDetourUseCase      $setOutboundsDetourUseCase,
        private readonly CreateConfigUseCase            $createConfigUseCase,
        private readonly SaveConfigUseCase              $saveSingBoxConfigUseCase,
        ConfigInstancePort                              $configInstancePort,
    )
    {
        parent::__construct($reporterPort, $configInstancePort);

    }

    protected function optionGroups(): array
    {
        return [
            new OutboundFilterOptionsGroup(true, self::URLTEST_FILTER_PREFIX),
            new CoreOptionsGroup()
        ];
    }


    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $subscription = $this->getSubscriptionWithNameUseCase->handle(
            $input->getArgument('name')
        );

        if ($subscription instanceof ConfigSubscription) {
            $this->saveSingBoxConfigUseCase->handle(new SaveConfigDTO($subscription->getConfigString()));


            return self::SUCCESS;
        }

        if (!($subscription instanceof OutboundsSubscription)) return self::FAILURE;

        if ($subscription->getOutbounds()->isEmpty()) throw new CriticalException("Not found outbounds for subscription");

        $subscriptionOutbounds = $subscription->getOutbounds();

        /**
         * Filter outbounds (main config group)
         */
        $mainFilters = $this->optionGroups->get(OutboundFilterOptionsGroup::class)->resolve();

        $subscriptionOutbounds = $this->filterOutboundsUseCase->handle(new FilterOutboundsDTO(
            $subscriptionOutbounds,
            criteria: $mainFilters->criteria,
            ignoreOutbounds: $mainFilters->ignoreOutbounds,
        ));


        /**
         * Create urltest outbounds
         *
         * NOTE: the urltest group has its own, fully independent copy of
         * every filter above (--urltestCountryCode, --urltestExcludeOutbound,
         * --urltestExceptOutbound, ...), applied only to the outbounds that
         * end up inside the urltest block - it never affects the main config
         * outbounds filtered above, and vice versa.
         */
        $urltestOutbounds = null;
        if ($input->getOption('urltest')) {
            $urltestOutbounds = clone $subscriptionOutbounds;

            $urltestFilters = $this->optionGroups->get(OutboundFilterOptionsGroup::class)->resolve(self::URLTEST_FILTER_PREFIX);

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
        if ($input->getOption('detourOutbound')) try {
            $subscriptionOutbounds = $this->setOutboundsDetourUseCase->handle(
                new SetOutboundsDetourDTO($subscriptionOutbounds, $subscriptionOutbounds->getWithTag($input->getOption('detourOutbound')))
            );
        } catch (OutboundNotFoundException) {
            throw new CriticalException("Outbound with tag '{$input->getOption('detourOutbound')}' not found");
        }


        $singBoxConfigJSON = $this->createConfigUseCase->handle(
            new CreateConfigDTO(
                $subscriptionOutbounds,
                $this->optionGroups->get(CoreOptionsGroup::class)->resolve(),
                $urltestOutbounds
            )
        );

        $this->saveSingBoxConfigUseCase->handle(new SaveConfigDTO($singBoxConfigJSON));

        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Subscription name')
            ->addOption('urltest', 'u', InputOption::VALUE_NONE, 'Add urltest outbound to config')
            ->addOption('detourOutbound', null, InputOption::VALUE_OPTIONAL, "Use the specified outbound as detour for all other outbounds");
    }
}
