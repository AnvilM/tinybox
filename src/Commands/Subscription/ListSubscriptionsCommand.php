<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\Subscription\GetSubscriptionListRepository;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use League\CLImate\CLImate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'subscription:list', description: 'List subscriptions', aliases: ['sub:list'])]
final class ListSubscriptionsCommand extends AbstractCommand
{

    public function __construct(
        ReporterPort                                   $reporterPort,
        private readonly GetSubscriptionListRepository $getSubscriptionListRepository,
        ConfigInstancePort                             $configInstancePort,
    )
    {
        parent::__construct($reporterPort, $configInstancePort);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        try {
            $subscriptionsMap = $this->getSubscriptionListRepository->getSubscriptionsList()->toNameUrlMap();
        } catch (UnableToGetListException $e) {
            throw new CriticalException ("Unable to get subscriptions list: " . $e->getMessage(), $e->getDebugMessage());
        }


        if ($subscriptionsMap->isEmpty()) throw new CriticalException("No subscriptions found");


        $table = [];
        foreach ($subscriptionsMap as $name => $url) {
            $table[] = [
                'name' => $name,
                'url' => $url,
            ];
        }

        new CLImate()->table($table);

        return self::SUCCESS;
    }
}