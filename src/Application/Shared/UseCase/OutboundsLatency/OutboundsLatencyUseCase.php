<?php

declare(strict_types=1);

namespace App\Application\Shared\UseCase\OutboundsLatency;

use App\Application\Outbound\DTO\OutboundLatencyDTO;
use App\Application\Shared\DTO\UseCase\OutboundsLatency\OutboundsLatencyDTO;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\OutboundTest\OutboundLatency\OutboundLatencyPort;
use App\Domain\Shared\VO\Config\SingBox\OutboundTest\Latency\LatencyTestMethod;
use App\Infrastructure\OutboundTest\OutboundLatency\Exception\UnableToGetLatencyException;
use App\Infrastructure\OutboundTest\Shared\CreateOutboundTestSingBoxConfig\Exception\CreateOutboundTestSingBoxConfigException;
use Psl\Collection\MutableVector;

final readonly class OutboundsLatencyUseCase
{
    public function __construct(
        private OutboundLatencyPort $outboundsLatencyPort,
        private ConfigInstancePort  $configInstancePort
    )
    {
    }

    /**
     * @param OutboundsLatencyDTO $dto
     *
     * @return MutableVector<OutboundLatencyDTO>
     *
     * @throws CriticalException
     */
    public function handle(OutboundsLatencyDTO $dto): MutableVector
    {
        try {
            return $this->outboundsLatencyPort->getOutboundsLatency(
                $dto->outbounds,
                LatencyTestMethod::tryFrom($dto->method ?? '')
                ?? $this->configInstancePort->get()->singBoxConfig->outboundTest->latency->method
            );
        } catch (UnableToSaveFileException|CreateOutboundTestSingBoxConfigException|UnableToGetLatencyException) {
            throw new CriticalException("Unable to test subscription outbounds");
        }
    }
}