<?php

declare(strict_types=1);

namespace App\Application\ReadSubscriptionList\Handler;

use App\Application\ReadSubscriptionList\Command\ReadSubscriptionsListCommandResult;
use App\Application\ReadSubscriptionList\Mapper\SubscriptionDTOMapper;
use App\Application\ReadSubscriptionList\Validator\SubscriptionsListValidator;
use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Exception\File\UnableToDecodeJSONException;
use App\Core\Shared\Exception\File\UnableToReadFileException;
use App\Core\Shared\Ports\Config\ConfigFactoryPort;
use App\Core\Shared\Ports\IO\File\ReadJsonFileNotifyPort;

final readonly class ReadSubscriptionsListHandler
{

    public function __construct(
        private SubscriptionsListValidator $subscriptionListValidation,
        private ReadJsonFileNotifyPort     $readJsonFileNotifyPort,
        private ConfigFactoryPort          $configFactoryPort,
        private SubscriptionDTOMapper      $subscriptionDTOMapper,
    )
    {
    }

    /**
     * Read subscriptions list from file
     *
     * @throws CriticalException
     */
    public function handle(): ReadSubscriptionsListCommandResult
    {
        try {
            $rawSubscriptionArray = $this->readJsonFileNotifyPort->notifyStartAndSuccess(
                "Reading subscriptions list file...",
                "Subscriptions list file successfully read",
            )->read($this->configFactoryPort->get()->subscriptionListPath);
        } catch (UnableToDecodeJSONException|UnableToReadFileException $e) {
            throw new CriticalException(
                ($e instanceof UnableToDecodeJSONException)
                    ? "Unable to parse JSON at subscriptions list"
                    : "Unable to read file at subscriptions list",
                $this->configFactoryPort->get()->subscriptionListPath
            );
        }

        $this->subscriptionListValidation->validate($rawSubscriptionArray);

        return new ReadSubscriptionsListCommandResult(
            $this->subscriptionDTOMapper->map(
                $rawSubscriptionArray
            ),
        );
    }
}