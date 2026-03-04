<?php

declare(strict_types=1);

namespace App\Commands;

use App\Application\AddSubscription\Command\AddSubscriptionCommand as AppendSubscriptionCommand;
use App\Application\AddSubscription\Handler\AddSubscriptionHandler;
use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Ports\IO\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\Shared\FatalErrorReporterEvent;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Throwable;

#[AsCommand(name: 'subscription:add', description: 'Add subscription')]
final class AddSubscriptionCommand extends Command
{
    public function __construct(
        private readonly ReporterPort           $reporterPort,
        private readonly AddSubscriptionHandler $addSubscriptionHandler,
    )
    {
        parent::__construct();
    }

    public function __invoke(InputInterface $input): int
    {
        try {
            $this->addSubscriptionHandler->handle(
                new AppendSubscriptionCommand(
                    $input->getArgument('subscriptionName'),
                    $input->getArgument('subscriptionUrl')
                )
            );
        } catch (CriticalException $e) {
            $this->reporterPort->notify(new FatalErrorReporterEvent(
                $e->getMessage(),
                $e->debugMessage ? DebugMessagesVO::create([$e->debugMessage]) : null
            ));

            return Command::FAILURE;
        } catch (Throwable $e) {
            $this->reporterPort->notify(new FatalErrorReporterEvent(
                "Unhandled exception",
                DebugMessagesVO::create([$e->getMessage()])
            ));
        }

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('subscriptionName', InputArgument::REQUIRED, 'Subscription name')
            ->addArgument('subscriptionUrl', InputArgument::REQUIRED, 'Subscription URL')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug messages');
    }
}