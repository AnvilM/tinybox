<?php

declare(strict_types=1);

namespace App\Commands;

use App\Application\FetchSubscriptions\Command\FetchSubscriptionsCommand;
use App\Application\FetchSubscriptions\Handler\FetchSubscriptionsHandler;
use App\Application\GenerateConfigs\Command\GenerateConfigsCommand;
use App\Application\GenerateConfigs\Handler\GenerateConfigsHandler;
use App\Application\ReadSubscriptionList\Handler\ReadSubscriptionsListHandler;
use App\Application\RunSingBox\Command\RunSingBoxCommand;
use App\Application\RunSingBox\Handler\RunSingBoxHandler;
use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Ports\IO\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\Shared\FatalErrorReporterEvent;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'subscription:apply', description: 'Apply subscription')]
final class ApplySubscriptionCommand extends Command
{
    public function __construct(
        private readonly GenerateConfigsHandler       $generateConfigsHandler,
        private readonly FetchSubscriptionsHandler    $fetchSubscriptionsHandler,
        private readonly ReadSubscriptionsListHandler $readSubscriptionsListHandler,
        private readonly RunSingBoxHandler            $runSingBoxHandler,
        private readonly ReporterPort                 $reporterPort,
    )
    {
        parent::__construct();
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        try {
            if ($input->getOption('update')) $this->generateConfigsHandler->handle(
                new GenerateConfigsCommand(
                    $this->fetchSubscriptionsHandler->handle(
                        new FetchSubscriptionsCommand(
                            $this->readSubscriptionsListHandler->handle()
                                ->rawSubscriptionCollectionDTO,
                            $input->getArgument('subscriptionName')
                        )
                    )
                ),
            );

            $this->runSingBoxHandler->handle(
                new RunSingBoxCommand(
                    $input->getArgument('subscriptionName'),
                    (bool)$input->getOption('systemd'),
                )
            );
        } catch (CriticalException $e) {
            $this->reporterPort->notify(new FatalErrorReporterEvent(
                $e->getMessage(),
                $e->debugMessage ? DebugMessagesVO::create([$e->debugMessage]) : null
            ));

            return Command::FAILURE;
        }

        return Command::SUCCESS;

    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'subscriptionName',
                InputArgument::REQUIRED,
                'Subscription name to apply'
            )
            ->addOption(
                'debug',
                'd',
                InputOption::VALUE_NONE,
                'Show debug messages'
            )->addOption(
                'update',
                'u',
                InputOption::VALUE_OPTIONAL,
                'Update subscription before applying',
                false
            )->addOption(
                'systemd',
                's',
                InputOption::VALUE_NONE,
                'Apply subscription using systemctl sing-box service'
            );
    }
}