<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

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

        if ($subscription->getOutbounds()->isEmpty()) throw new CriticalException("Not found schemes for subscription");

        $subscriptionOutbounds = $subscription->getOutbounds();

        if ($input->getOption('exclude')) {
            $filterOutboundsDTO = new FilterOutboundsDTO($subscriptionOutbounds, new Vector($input->getOption('exclude')));
        }

        if ($input->getOption('excludeCountryCode')) {
            $filterCountryCodesDTO = new FilterExcludeCountryCodesDTO(
                new Vector($input->getOption('excludeCountryCode')),
                $input->getOption('excludeCountryCodeExcept') ? new Vector($input->getOption('excludeCountryCodeExcept')) : null,
                $input->getOption('excludeCountryForce')
            );

            if (!isset($filterOutboundsDTO)) $filterOutboundsDTO = new FilterOutboundsDTO($subscriptionOutbounds, null, $filterCountryCodesDTO);
            else $filterOutboundsDTO->setFilterExcludeCountryCodesDTO($filterCountryCodesDTO);
        }

        if (isset($filterOutboundsDTO)) $subscriptionOutbounds = $this->filterOutboundsUseCase->handle($filterOutboundsDTO);

        $urltestOutbounds = null;
        if ($input->getOption('urltest')) {
            $urltestOutbounds = clone $subscriptionOutbounds;

            if ($input->getOption('urltestExclude')) {
                $urltestOutbounds = $this->filterOutboundsUseCase->handle(new FilterOutboundsDTO($urltestOutbounds, new Vector($input->getOption('urltestExclude'))));
            }
        }

        if ($input->getOption('detour')) try {
            $subscriptionOutbounds = $this->setOutboundsDetourUseCase->handle(
                new SetOutboundsDetourDTO($subscriptionOutbounds, $subscriptionOutbounds->getWithId($input->getOption('detour')))
            );
        } catch (OutboundNotFoundException) {
            throw new CriticalException("Outbound with tag '{$input->getOption('detour')}' not found");
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
            ->addOption('excludeCountryCode', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Dont apply outbounds with ip in specified country Example: --denyCountry=US (NOTE: If outbound is unavailable it will be not denied).')
            ->addOption('urltest', 'u', InputOption::VALUE_NONE, 'Add to config urltest outbound')
            ->addOption('detour', null, InputOption::VALUE_OPTIONAL, "Scheme id, to use as detour for all outbounds")
            ->addOption('urltestExclude', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Scheme id, to exclude from urltest outbound')
            ->addOption('exclude', 'e', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, "Scheme id, to exclude")
            ->addOption('excludeCountryCodeExcept', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, "Scheme id, to except in country exclude")
            ->addOption('excludeCountryForce', null, InputOption::VALUE_NONE);
    }
}