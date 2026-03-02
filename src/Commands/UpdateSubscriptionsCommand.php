<?php

declare(strict_types=1);

namespace App\Commands;

use App\Application\FetchSubscriptions\Command\FetchSubscriptionsCommand;
use App\Application\FetchSubscriptions\Handler\FetchSubscriptionsHandler;
use App\Application\GenerateConfigs\Command\GenerateConfigsCommand;
use App\Application\GenerateConfigs\Handler\GenerateConfigsHandler;
use App\Application\ReadSubscriptionList\Handler\ReadSubscriptionsListHandler;
use App\Application\RunSingBoxWithConfig\Command\RunSingBoxWithConfigCommand;
use App\Application\RunSingBoxWithConfig\Handler\RunSingBoxWithConfigHandler;
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


#[AsCommand(name: 'subscription:update', description: 'Update subscriptions')]
final  class UpdateSubscriptionsCommand extends Command
{
    public function __construct(
        private readonly FetchSubscriptionsHandler    $fetchSubscriptionsHandler,
        private readonly ReporterPort                 $reporterPort,
        private readonly GenerateConfigsHandler       $generateConfigHandler,
        private readonly RunSingBoxWithConfigHandler  $runSingBoxWithConfigHandler,
        private readonly ReadSubscriptionsListHandler $readSubscriptionsListHandler,
    )
    {
        parent::__construct();
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        try {


            $this->generateConfigHandler->handle(
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

            $subscriptionName = $input->getArgument('subscriptionName');
            $applyOption = $input->getOption('apply');

            $subscription =
                ($subscriptionName && $applyOption !== false) ? $subscriptionName :
                    (!$subscriptionName && $applyOption ? $applyOption : null);

            if ($subscription !== null) {
                return $this->runSingBoxWithConfigHandler
                    ->handle(new RunSingBoxWithConfigCommand($subscription))
                    ->responseCode;
            }


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
                InputArgument::OPTIONAL,
                'Subscription name to update'
            )
            ->addOption(
                'debug',
                'd',
                InputOption::VALUE_NONE,
                'Show debug messages'
            )->addOption(
                'apply',
                'a',
                InputOption::VALUE_OPTIONAL,
                'Subscription name to apply',
                false
            );
    }
}