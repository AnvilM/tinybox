<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Application\Services\Subscription\ApplySubscription\Handler\ApplySubscriptionHandler;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'subscription:apply', description: 'Apply subscription', aliases: ['sub:apply'])]
final class ApplySubscriptionCommand extends AbstractCommand
{
    public function __construct(
        ReporterPort                              $reporterPort,
        private readonly ApplySubscriptionHandler $applySubscriptionHandler,
    )
    {
        parent::__construct($reporterPort);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $this->applySubscriptionHandler->handle(
            new \App\Application\Services\Subscription\ApplySubscription\Command\ApplySubscriptionCommand(
                $input->getArgument('name'),
                $input->getOption('systemd'),
                $input->getOption('denyCountry') != null ? trim($input->getOption('denyCountry')) != "" ? $input->getOption('denyCountry') : null : null,
                $input->getOption('urltest'),
                $input->getOption('detour') != null ? trim($input->getOption('detour')) != "" ? $input->getOption('detour') : null : null,
                $input->getOption('exclude') != null ? trim($input->getOption('exclude')) != "" ? $input->getOption('exclude') : null : null,
                $input->getOption('urltestExclude') != null ? trim($input->getOption('urltestExclude')) != "" ? $input->getOption('urltestExclude') : null : null,
                $input->getOption('excludeCountryExcept') != null ? trim($input->getOption('excludeCountryExcept')) != "" ? $input->getOption('excludeCountryExcept') : null : null,
                $input->getOption('excludeCountryForce'),
            )
        );

        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Subscription name')
            ->addOption('systemd', 's', InputOption::VALUE_NONE, 'If true config will be saved as systemd sing-box config and sing-box systemd service will be restarted')
            ->addOption('denyCountry', null, InputOption::VALUE_OPTIONAL, 'Dont apply outbounds with ip in specified country Example: --denyCountry=US (NOTE: If outbound is unavailable it will be not denied).')
            ->addOption('urltest', 'u', InputOption::VALUE_NONE, 'Add to config urltest outbound')
            ->addOption('detour', null, InputOption::VALUE_OPTIONAL, "Scheme id, to use as detour for all outbounds")
            ->addOption('urltestExclude', null, InputOption::VALUE_OPTIONAL, 'Scheme id, to exclude from urltest outbound')
            ->addOption('exclude', 'e', InputOption::VALUE_OPTIONAL, "Scheme id, to exclude")
            ->addOption('excludeCountryExcept', null, InputOption::VALUE_OPTIONAL, "Scheme id, to except in country exclude")
            ->addOption('excludeCountryForce', null, InputOption::VALUE_NONE)
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug messages');
    }
}