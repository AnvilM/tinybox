<?php

declare(strict_types=1);

namespace App\Application\GenerateConfigs\Handler;

use App\Application\GenerateConfigs\Command\GenerateConfigsCommand;
use App\Application\GenerateConfigs\Mapper\RawSchemesMapper;
use App\Application\GenerateConfigs\Parser\RawSchemesParser;
use App\Core\Domain\Scheme\Collection\SchemeMap;
use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Ports\IO\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\GenerateConfigs\Handler\GenerateConfigsHandler\NoValidSchemesInSubscriptionFoundReporterEvent;
use InvalidArgumentException;

final readonly class GenerateConfigsHandler
{
    public function __construct(
        private RawSchemesMapper    $rawSchemesMapper,
        private RawSchemesParser    $rawSchemesParser,
        private ReporterPort        $reporterPort,
        private CreateSingBoxConfig $createSingBoxConfig,
    )
    {
    }

    /**
     * Generate sing-box config from subscription name and raw schemes string
     *
     * @param GenerateConfigsCommand $command Command with subscription name and raw schemes string
     *
     * @throws CriticalException Throws if no valid schemes in all raw schemes strings found
     */
    public function handle(GenerateConfigsCommand $command): void
    {
        $schemeMap = new SchemeMap();

        foreach ($command->rawSchemesArray as $subscriptionName => $rawSchemesString) {
            try {
                $schemeMap[$subscriptionName] = $this->rawSchemesMapper->map(
                    $this->rawSchemesParser->parse(
                        $rawSchemesString,
                        $subscriptionName
                    ), $subscriptionName
                );
            } catch (InvalidArgumentException) {
                $this->reporterPort->notify(new NoValidSchemesInSubscriptionFoundReporterEvent(
                    $subscriptionName,
                ));
            }
        }

        if ($schemeMap->isEmpty()) {
            throw new CriticalException("No valid subscriptions found");
        }

        $this->createSingBoxConfig->create(
            $schemeMap
        );
    }
}