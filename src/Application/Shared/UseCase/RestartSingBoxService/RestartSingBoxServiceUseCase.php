<?php

declare(strict_types=1);

namespace App\Application\Shared\UseCase\RestartSingBoxService;

use App\Application\Services\Subscription\ApplySubscription\Exception\UnableToRestartSingBoxServiceException;
use App\Application\Shared\UseCase\RestartSingBoxService\Process\RestartSingBoxService;

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