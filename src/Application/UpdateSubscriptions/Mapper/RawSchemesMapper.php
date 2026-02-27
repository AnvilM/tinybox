<?php

declare(strict_types=1);

namespace App\Application\UpdateSubscriptions\Mapper;

use App\Core\Domain\Scheme\Collection\SchemeCollection;
use App\Core\Domain\Scheme\Exception\UnsupportedSchemeType;
use App\Core\Domain\Scheme\Factory\SchemeFactory;
use App\Core\Domain\Scheme\VO\RawSchemeVO;
use App\Core\Shared\Ports\IO\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\UpdateSubscriptionsLifecycle\Mapper\RawSchemesMapper\InvalidSchemeReporterEvent;
use App\Core\Shared\ReporterEvent\Events\UpdateSubscriptionsLifecycle\Mapper\RawSchemesMapper\UnsupportedSchemeReporterEvent;
use InvalidArgumentException;

final readonly class RawSchemesMapper
{
    public function __construct(
        private ReporterPort $reporterPort,
    )
    {
    }

    /**
     * Maps array of rawSchemeVO to schemes collection
     *
     * @param RawSchemeVO[] $rawSchemeVOArray Array of rawSchemeVO
     * @param string $subscriptionName Subscription name for reporter event
     *
     * @return SchemeCollection Schemes collection
     *
     * @throws InvalidArgumentException Throws if no one scheme in subscription is valid
     */
    public function map(array $rawSchemeVOArray, string $subscriptionName): SchemeCollection
    {
        $schemesArray = [];

        foreach ($rawSchemeVOArray as $rawSchemeVO) {
            try {
                $schemesArray[] = SchemeFactory::fromRawSchemeVO($rawSchemeVO);
            } catch (InvalidArgumentException $exception) {
                $this->reporterPort->notify(new InvalidSchemeReporterEvent(
                    $rawSchemeVO, $subscriptionName, $exception->getMessage()
                ));
            } catch (UnsupportedSchemeType $exception) {
                $this->reporterPort->notify(new UnsupportedSchemeReporterEvent(
                    $rawSchemeVO, $subscriptionName, $exception->getMessage()
                ));
            }
        }

        return SchemeCollection::create($schemesArray);
    }
}