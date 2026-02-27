<?php

declare(strict_types=1);

namespace App\Commands;

use App\Application\UpdateSubscriptions\Command\UpdateSubscriptionsCommand;
use App\Application\UpdateSubscriptions\Handler\UpdateSubscriptionsHandler;
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


#[AsCommand(name: 'update')]
final  class UpdateCommand extends Command
{
    public function __construct(
        private readonly UpdateSubscriptionsHandler $updateSubscriptionsHandler,
        private readonly ReporterPort               $reporterPort,
    )
    {
        parent::__construct();
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->updateSubscriptionsHandler->handle(
                new UpdateSubscriptionsCommand($input->getArgument('subscriptionName')),
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
                InputArgument::OPTIONAL,
                'Subscription name to update'
            )
            ->addOption(
                'debug',
                'd',
                InputOption::VALUE_NONE,
                'Show debug messages'
            );
    }
}