<?php

declare(strict_types=1);

namespace App\Application\Services\Subscription\ApplySubscription\RestartSingBoxService;

use App\Application\Services\Subscription\ApplySubscription\Exception\UnableToRestartSingBoxServiceException;
use App\Application\Services\Subscription\ApplySubscription\RestartSingBoxService\Process\RestartSingBoxService;

final readonly class RestartSingBoxServiceUseCase
{
    public function __construct(
        private RestartSingBoxService $restartSingBoxService
    )
    {
    }

    /**
     * @throws UnableToRestartSingBoxServiceException
     */
    public function handle(): void
    {
        $this->restartSingBoxService->restart();
    }
}