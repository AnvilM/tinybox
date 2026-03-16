<?php

declare(strict_types=1);

namespace App\Application\Shared\Subscription\Shared\File;

use App\Domain\Shared\Exception\CriticalException;
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
     * @param SubscriptionsMap $subscriptionsMap Subscriptions map
     *
     * @throws CriticalException
     */
    public function write(SubscriptionsMap $subscriptionsMap): void
    {
        $path = $this->configInstancePort->get()->subscriptionsListPath;

        try {
            $this->saveFileNotifyPort->notifyStartAndSuccess(
                "Saving subscriptions...",
                "Schemes successfully saved",
            )->save($path, $subscriptionsMap->toJson());
        } catch (UnableToSaveFileException|UnableToEncodeJsonException) {
            throw new CriticalException("Unable to save subscriptions", $path);
        }


    }
}