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
use Iva\Input\Option;
use Iva\Output\Output;

final class ListSubscriptionsCommand extends AbstractCommand
{
    private Option $json;

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

        $this->json = $this->addOption(Option::flag(
            name: 'json',
            shortcut: 'j',
            description: 'JSON output',
        ));
    }

    protected function handle(Input $input, Output $output): int
    {
        try {
            $subscriptionsMap = $this->getSubscriptionListRepository->getSubscriptionsList()->toNameUrlMap();
        } catch (UnableToGetListException $e) {
            throw new CriticalException("Unable to get subscriptions list" . (trim($e->getMessage()) != '' ? ": {$e->getMessage()}" : ''), $e->getDebugMessage());
        }


        if ($subscriptionsMap->isEmpty()) throw new CriticalException("No subscriptions found");

        if ($this->isJson($input)) {
            $array = [];
            foreach ($subscriptionsMap as $name => $url) {
                $array[] = ['name' => $name, 'url' => $url];
            }

            $output->write(json_encode($array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        } else {
            $table = $output->table()->headers(['name', 'url']);

            foreach ($subscriptionsMap as $name => $url) {
                $table->row([$name, $url]);
            }

            $table->render();
        }

        return ExitCode::Ok->value;
    }


    private function isJson(Input $input): bool
    {
        return $input->flag($this->json);
    }
}
