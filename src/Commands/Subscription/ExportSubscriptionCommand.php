<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Application\Outbound\DTO\UseCase\FilterOutbounds\FilterOutboundsDTO;
use App\Application\Outbound\DTO\UseCase\OverrideOutbounds\OverrideOutboundDTO;
use App\Application\Outbound\DTO\UseCase\SetOutboundsDetour\SetOutboundsDetourDTO;
use App\Application\Outbound\Filter\Criteria\OutboundCoreSupportCriteria;
use App\Application\Outbound\Mapper\ToSchemeString\ToSchemeStringOutboundMapper;
use App\Application\Outbound\UseCase\FilterOutbounds\FilterOutboundsUseCase;
use App\Application\Outbound\UseCase\OverrideOutbounds\OverrideOutboundsUseCase;
use App\Application\Outbound\UseCase\SetOutboundsDetour\SetOutboundsDetourUseCase;
use App\Application\Shared\DTO\UseCase\CreateConfig\CreateConfigDTO;
use App\Application\Shared\DTO\UseCase\SaveConfig\SaveConfigDTO;
use App\Application\Shared\UseCase\CreateConfig\CreateConfigUseCase;
use App\Application\Shared\UseCase\SaveSingBoxConfig\SaveConfigUseCase;
use App\Application\Subscription\UseCase\GetSubscriptionWithName\GetSubscriptionWithNameUseCase;
use App\Commands\AbstractCommand;
use App\Commands\Shared\OptionGroup\Groups\CoreOptionsGroup;
use App\Commands\Shared\OptionGroup\Groups\OutboundFilterOptionsGroup;
use App\Commands\Shared\OptionGroup\Groups\OverridesOptionsGroup;
use App\Domain\Outbound\Exception\OutboundNotFoundException;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use App\Domain\Subscription\Entity\ConfigSubscription;
use App\Domain\Subscription\Entity\OutboundsSubscription;
use Psl\Collection\MutableVector;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'subscription:export', description: 'Export subscription outbounds', aliases: ['sub:export'])]
final class ExportSubscriptionCommand extends AbstractCommand
{

    private const string URLTEST_FILTER_PREFIX = 'urltest';

    public function __construct(
        ReporterPort                                    $reporterPort,
        private readonly GetSubscriptionWithNameUseCase $getSubscriptionWithNameUseCase,
        private readonly FilterOutboundsUseCase         $filterOutboundsUseCase,
        private readonly SetOutboundsDetourUseCase      $setOutboundsDetourUseCase,
        private readonly CreateConfigUseCase            $createConfigUseCase,
        private readonly SaveConfigUseCase              $saveSingBoxConfigUseCase,
        private readonly OverrideOutboundsUseCase       $overrideOutboundsUseCase,
        private readonly ToSchemeStringOutboundMapper   $toSchemeStringOutboundMapper,
        ConfigInstancePort                              $configInstancePort,
    )
    {
        parent::__construct($reporterPort, $configInstancePort);

    }

    protected function optionGroups(): array
    {
        return [
            new OutboundFilterOptionsGroup(true, self::URLTEST_FILTER_PREFIX),
            new CoreOptionsGroup(),
            new OverridesOptionsGroup()
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

        $criteria = $this->exportAsScheme($input) ? $mainFilters->criteria : new MutableVector($mainFilters->criteria->toArray())
            ->add(new OutboundCoreSupportCriteria(
                $this->optionGroups->get(CoreOptionsGroup::class)->resolve()->toCoreType()
            ));

        $subscriptionOutbounds = $this->filterOutboundsUseCase->handle(new FilterOutboundsDTO(
            $subscriptionOutbounds,
            criteria: $criteria,
            ignoreOutbounds: $mainFilters->ignoreOutbounds,
        ));


        $subscriptionOutbounds = $this->overrideOutboundsUseCase->override(
            new OverrideOutboundDTO(
                $subscriptionOutbounds,
                $this->optionGroups->get(OverridesOptionsGroup::class)->getUUID(),
                $this->optionGroups->get(OverridesOptionsGroup::class)->getSSPass(),
            )
        );

        if ($subscriptionOutbounds->isEmpty()) throw new CriticalException("No outbound calls matching the filter criteria were found");

        if (!$this->exportAsScheme($input)) {
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
        } else {
            $singBoxConfigJSON = "";
            foreach ($subscriptionOutbounds->getOutbounds() as $outbound) {
                $singBoxConfigJSON .= $this->toSchemeStringOutboundMapper->map($outbound) . "\n";
            }
        }

        $this->saveSingBoxConfigUseCase->handle(new SaveConfigDTO($singBoxConfigJSON));

        return self::SUCCESS;
    }

    private function exportAsScheme(InputInterface $input): bool
    {
        return ($input->getOption('scheme'));
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Subscription name')
            ->addOption('urltest', 'u', InputOption::VALUE_NONE, 'Add urltest outbound to config')
            ->addOption('detourOutbound', null, InputOption::VALUE_OPTIONAL, "Use the specified outbound as detour for all other outbounds")
            ->addOption('scheme', null, InputOption::VALUE_NONE, 'Export subscription outbounds as schemes')
            ->addOption('config', null, InputOption::VALUE_NONE, 'Export subscription outbounds as config');
    }
}
