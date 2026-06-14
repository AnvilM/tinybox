<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Application\Shared\DTO\UseCase\FilterOutbounds\FilterCountryCodesDTO;
use App\Application\Shared\DTO\UseCase\FilterOutbounds\FilterExcludeCountryCodesDTO;
use App\Application\Shared\DTO\UseCase\FilterOutbounds\FilterOutboundsDTO;
use App\Application\Shared\DTO\UseCase\SaveSingBoxConfig\SaveSingBoxConfigDTO;
use App\Application\Shared\DTO\UseCase\SetOutboundsDetour\SetOutboundsDetourDTO;
use App\Application\Shared\UseCase\CreateSingBoxConfig\CreateSingBoxConfigUseCase;
use App\Application\Shared\UseCase\FilterOutbounds\FilterOutboundsUseCase;
use App\Application\Shared\UseCase\RestartSingBoxService\RestartSingBoxServiceUseCase;
use App\Application\Shared\UseCase\SaveSingBoxConfig\SaveSingBoxConfigUseCase;
use App\Application\Shared\UseCase\SetOutboundsDetour\SetOutboundsDetourUseCase;
use App\Application\Subscription\UseCase\GetSubscriptionWithName\GetSubscriptionWithNameUseCase;
use App\Commands\AbstractCommand;
use App\Domain\Outbound\Exception\OutboundNotFoundException;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use App\Domain\Subscription\Entity\ConfigSubscription;
use App\Domain\Subscription\Entity\OutboundsSubscription;
use Psl\Collection\Vector;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'subscription:apply', description: 'Apply subscription', aliases: ['sub:apply'])]
final class ApplySubscriptionCommand extends AbstractCommand
{
    public function __construct(
        ReporterPort                                    $reporterPort,
        private readonly GetSubscriptionWithNameUseCase $getSubscriptionWithNameUseCase,
        private readonly FilterOutboundsUseCase         $filterOutboundsUseCase,
        private readonly SetOutboundsDetourUseCase      $setOutboundsDetourUseCase,
        private readonly CreateSingBoxConfigUseCase     $createSingBoxConfigUseCase,
        private readonly SaveSingBoxConfigUseCase       $saveSingBoxConfigUseCase,
        private readonly RestartSingBoxServiceUseCase   $restartSingBoxServiceUseCase,
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
            $this->saveSingBoxConfigUseCase->handle(new SaveSingBoxConfigDTO($subscription->getConfigString()));

            $this->restartSingBoxServiceUseCase->handle();

            return self::SUCCESS;
        }

        if (!($subscription instanceof OutboundsSubscription)) return self::FAILURE;

        if ($subscription->getOutbounds()->isEmpty()) throw new CriticalException("Not found outbounds for subscription");

        $subscriptionOutbounds = $subscription->getOutbounds();

        /**
         * Filter outbounds
         */
        $subscriptionOutbounds = $this->filterOutboundsUseCase->handle(new FilterOutboundsDTO(
            $subscriptionOutbounds,
            ignoreOutbounds: $input->getOption('exceptOutbound') ? new Vector($input->getOption('exceptOutbound')) : null,
            excludeOutbounds: $input->getOption('excludeOutbound') ? new Vector($input->getOption('excludeOutbound')) : null,
            filterExcludeCountryCodesDTO: $input->getOption('excludeCountryCode') ? new FilterExcludeCountryCodesDTO(
                new Vector($input->getOption('excludeCountryCode')),
                $input->getOption('countryOutboundIpFallback'),
                $input->getOption('countryOnlyAvailable')
            ) : null,
            filterCountryCodesDTO: $input->getOption('countryCode') ? new FilterCountryCodesDTO(
                new Vector($input->getOption('countryCode')),
                $input->getOption('countryOutboundIpFallback'),
                $input->getOption('countryOnlyAvailable')
            ) : null,
            filterExcludeOutboundTypes: $input->getOption('excludeOutboundType') ? new Vector($input->getOption('excludeOutboundType')) : null,
            filterOutboundTypes: $input->getOption('outboundType') ? new Vector($input->getOption('outboundType')) : null
        ));


        /**
         * Create urltest outbounds
         */
        $urltestOutbounds = null;
        if ($input->getOption('urltest')) {
            $urltestOutbounds = clone $subscriptionOutbounds;

            if ($input->getOption('urltestExclude')) {
                $urltestOutbounds = $this->filterOutboundsUseCase->handle(new FilterOutboundsDTO($urltestOutbounds, null, new Vector($input->getOption('urltestExclude'))));
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


        $singBoxConfigJSON = $this->createSingBoxConfigUseCase->handle(
            $subscriptionOutbounds, $urltestOutbounds
        );

        $this->saveSingBoxConfigUseCase->handle(new SaveSingBoxConfigDTO($singBoxConfigJSON));

        $this->restartSingBoxServiceUseCase->handle();

        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Subscription name')
            ->addOption('excludeCountryCode', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Filter outbounds and use only those whose country code does not match the specified one')
            ->addOption('urltest', 'u', InputOption::VALUE_NONE, 'Add urltest outbound to config ')
            ->addOption('detourOutbound', null, InputOption::VALUE_OPTIONAL, "Use the specified outbound as detour for all other outbounds")
            ->addOption('urltestExclude', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'One or more outbounds that will not be included in urltest outbound if the -u or --urltest flag is specified')
            ->addOption('excludeOutbound', 'e', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, "One or more outbounds to be excluded")
            ->addOption('exceptOutbound', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, "One or more outbounds that will be ignored by all filters")
            ->addOption('countryOutboundIpFallback', null, InputOption::VALUE_NONE, "Use the outbound IP specified in the configuration if its real IP could not be obtained")
            ->addOption('countryCode', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Filter outbounds and use only those whose country code match the specified one')
            ->addOption('countryOnlyAvailable', null, InputOption::VALUE_NONE, "Exclude all outbounds for which the country code could not be obtained")
            ->addOption('excludeOutboundType', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL)
            ->addOption('outboundType', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL);
    }
}