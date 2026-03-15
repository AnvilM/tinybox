<?php

declare(strict_types=1);

namespace App\Application\ListConfigs\Handler;

use App\Application\Shared\Config\UseCase\ReadConfigsList\ReadConfigsListUseCase;
use App\Domain\Shared\Exception\CriticalException;
use Psl\Collection\MutableVector;

final readonly class ListConfigsHandler
{
    public function __construct(
        private ReadConfigsListUseCase $readConfigsListUseCase,
    )
    {
    }

    /**
     * @return MutableVector<string> Vector of config names
     *
     * @throws CriticalException
     */
    public function handle(): MutableVector
    {
        return $this->readConfigsListUseCase->handle()->getConfigNames();
    }
}