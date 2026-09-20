<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Application\Repository\Shared\Exception\UnableToGetListException;
use App\Application\Repository\Subscription\GetSubscriptionListRepository;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterInstancePort;
use Iva\ExitCode;
use Iva\Input\Input;
use Iva\Output\Output;

final class ListSubscriptionsCommand extends AbstractCommand
{
    public function __construct(
        ReporterInstancePort                           $reporterInstancePort,
        private readonly GetSubscriptionListRepository $getSubscriptionListRepository,
        ConfigInstancePort                             $configInstancePort,
    )
    {
        parent::__construct($reporterInstancePort, $configInstancePort);
    }

    protected function configureCommand(): void
    {
        $this->setName('list');
        $this->setDescription('List subscriptions');
    }

    protected function handle(Input $input, Output $output): int
    {
        try {
            $subscriptionsMap = $this->getSubscriptionListRepository->getSubscriptionsList()->toNameUrlMap();
        } catch (UnableToGetListException $e) {
            throw new CriticalException("Unable to get subscriptions list: " . $e->getMessage(), $e->getDebugMessage());
        }


        if ($subscriptionsMap->isEmpty()) throw new CriticalException("No subscriptions found");


        // Ported from League\CLImate's table(array $rows) — Iva's Output::table() builds the same
        // box-drawn table from explicit headers()/row() calls instead of associative-array rows.
        $table = $output->table()->headers(['name', 'url']);

        foreach ($subscriptionsMap as $name => $url) {
            $table->row([$name, $url]);
        }

        $table->render();

        return ExitCode::Ok->value;
    }
}
