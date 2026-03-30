<?php

declare(strict_types=1);

namespace App\Application\Repository\Subscription\Shared\File;

use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\File\SaveFileNotifyPort;
use App\Domain\Subscription\Collection\SubscriptionsMap;

final readonly class WriteSubscriptions
{
    public function __construct(
        private SaveFileNotifyPort $saveFileNotifyPort,
        private ConfigInstancePort $configInstancePort,
    )
    {
    }

    /**
     * Write subscriptions list to file
     *
     * @param SubscriptionsMap $subscriptionsMap Subscriptions list
     *
     * @throws UnableToEncodeJsonException If unable to convert subscriptions list to JSON
     * @throws UnableToSaveFileException If unable to save subscriptions list to file
     */
    public function write(SubscriptionsMap $subscriptionsMap): void
    {
        $path = $this->configInstancePort->get()->subscriptionsListPath;

        $this->saveFileNotifyPort->notifyStartAndSuccess(
            "Saving subscriptions...",
            "Subscriptions successfully saved",
        )->save($path, $subscriptionsMap->toJson());


    }
}