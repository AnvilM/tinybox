<?php

declare(strict_types=1);

namespace App\Application\AddSubscription\Handler;

use App\Application\AddSubscription\Command\AddSubscriptionCommand;
use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Exception\File\UnableToDecodeJSONException;
use App\Core\Shared\Exception\File\UnableToReadFileException;
use App\Core\Shared\Exception\File\UnableToSaveFileException;
use App\Core\Shared\Ports\Config\ConfigFactoryPort;
use App\Core\Shared\Ports\IO\File\ReadJsonFileNotifyPort;
use App\Core\Shared\Ports\IO\File\SaveFileNotifyPort;
use JsonException;

final readonly class AddSubscriptionHandler
{
    public function __construct(
        private ReadJsonFileNotifyPort $readJsonFileNotifyPort,
        private ConfigFactoryPort      $configFactoryPort,
        private SaveFileNotifyPort     $saveFileNotifyPort,
    )
    {
    }

    public function handle(AddSubscriptionCommand $command): void
    {
        if (trim($command->subscriptionName) === "")
            throw new CriticalException("Invalid subscription name", $command->subscriptionName);

        if (trim($command->subscriptionUrl) === "")
            throw new CriticalException("Invalid subscription url", $command->subscriptionUrl);

        try {
            $subscriptionArray = $this->readJsonFileNotifyPort->notifyStartAndSuccess(
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

        $subscriptionArray[$command->subscriptionName] = $command->subscriptionUrl;

        try {
            $subscriptionsListJson = json_encode($subscriptionArray, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $e) {
            throw new CriticalException("Unable to encode JSON at subscriptions list", $e->getMessage());
        }

        try {
            $this->saveFileNotifyPort->notifyStartAndSuccess(
                "Saving subscriptions list file...",
                "Subscriptions list file successfully saved",
            )->save($this->configFactoryPort->get()->subscriptionListPath, $subscriptionsListJson);
        } catch (UnableToSaveFileException) {
            throw new CriticalException("Unable to save subscriptions list file", $this->configFactoryPort->get()->subscriptionListPath);
        }

    }
}