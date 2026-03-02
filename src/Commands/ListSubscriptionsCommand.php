<?php

declare(strict_types=1);

namespace App\Commands;

use App\Application\ReadSubscriptionList\Handler\ReadSubscriptionsListHandler;
use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Ports\IO\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\Shared\FatalErrorReporterEvent;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use League\CLImate\CLImate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'subscription:list', description: 'List all subscriptions')]
final readonly class ListSubscriptionsCommand
{
    public function __construct(
        private ReadSubscriptionsListHandler $readSubscriptionsListHandler,
        private ReporterPort                 $reporterPort,
    )
    {
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        try {
            foreach (
                $this->readSubscriptionsListHandler->handle()->rawSubscriptionCollectionDTO
                as $subscriptionDTO
            ) {
                new CLImate()->green()->out("[+] $subscriptionDTO->subscriptionName");
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


}