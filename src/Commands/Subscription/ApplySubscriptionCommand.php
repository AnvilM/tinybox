<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Application\Outbound\DTO\UseCase\FilterOutbounds\FilterOutboundsDTO;
use App\Application\Outbound\DTO\UseCase\SetOutboundsDetour\SetOutboundsDetourDTO;
use App\Application\Outbound\UseCase\FilterOutbounds\FilterOutboundsUseCase;
use App\Application\Outbound\UseCase\SetOutboundsDetour\SetOutboundsDetourUseCase;
use App\Application\Shared\DTO\UseCase\CreateConfig\ConfigType;
use App\Application\Shared\DTO\UseCase\CreateConfig\CreateConfigDTO;
use App\Application\Shared\DTO\UseCase\SaveConfig\SaveConfigDTO;
use App\Application\Shared\UseCase\CreateConfig\CreateConfigUseCase;
use App\Application\Shared\UseCase\SaveSingBoxConfig\SaveConfigUseCase;
use App\Application\Subscription\UseCase\GetSubscriptionWithName\GetSubscriptionWithNameUseCase;
use App\Commands\AbstractCommand;
use App\Commands\Shared\OutboundFilter\OutboundFilterOptionsBinder;
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
    /**
     * Every outbound-filter option registered by OutboundFilterOptionsBinder
     * is duplicated under this prefix (--urltestCountryCode, --urltestExcludeOutbound, ...)
     * to filter the urltest group completely independently from the main config.
     */
    private const string URLTEST_FILTER_PREFIX = 'urltest';

    public function __construct(
        ReporterPort                                    $reporterPort,
        private readonly GetSubscriptionWithNameUseCase $getSubscriptionWithNameUseCase,
        private readonly FilterOutboundsUseCase         $filterOutboundsUseCase,
        private readonly SetOutboundsDetourUseCase      $setOutboundsDetourUseCase,
        private readonly CreateConfigUseCase            $createConfigUseCase,
        private readonly SaveConfigUseCase              $saveSingBoxConfigUseCase,
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
            $this->saveSingBoxConfigUseCase->handle(new SaveConfigDTO($subscription->getConfigString()));


            return self::SUCCESS;
        }

        if (!($subscription instanceof OutboundsSubscription)) return self::FAILURE;

        if ($subscription->getOutbounds()->isEmpty()) throw new CriticalException("Not found outbounds for subscription");

        $subscriptionOutbounds = $subscription->getOutbounds();

        /**
         * Filter outbounds (main config group)
         */
        $mainFilters = $this->outboundFilterOptionsBinder->resolve($input);

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

            $urltestFilters = $this->outboundFilterOptionsBinder->resolve($input, self::URLTEST_FILTER_PREFIX);

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
                $input->getOption('sing-box')
                    ? ConfigType::SingBox
                    : ($input->getOption('xray') ? ConfigType::Xray : ConfigType::SingBox),
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
            ->addOption('detourOutbound', null, InputOption::VALUE_OPTIONAL, "Use the specified outbound as detour for all other outbounds")
            ->addOption('sing-box', 's', InputOption::VALUE_NONE, 'Generate config for sing box format. Sing box format using by default')
            ->addOption('xray', 'x', InputOption::VALUE_NONE, 'Generate config for xray format. Sing box format is used by default');

        /**
         * Registers the *entire* filtering option set (see
         * OutboundFilterOptionsBinder) twice: once for the main config
         * outbounds, once more prefixed with "urltest" for the urltest
         * group. Adding a new filter rule to the binder makes it appear
         * here automatically, for both groups, with zero changes in this
         * command.
         */
        $this->outboundFilterOptionsBinder->configure($this);
        $this->outboundFilterOptionsBinder->configure($this, self::URLTEST_FILTER_PREFIX);
    }
}
