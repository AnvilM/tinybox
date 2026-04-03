<?php

declare(strict_types=1);

namespace App\Application\Shared\DTO\UseCase\FilterOutbounds;

use App\Domain\Outbound\Entity\Outbound;
use Psl\Collection\VectorInterface;

final readonly class FilterCountryCodesDTO
{
    /**
     * @param VectorInterface<string> $excludeCountryCodes Exclude outbounds with ips in provided countries
     * @param null|VectorInterface<Outbound> $exceptOutbounds Except outbounds to exclude
     * @param bool $force Check outbound ip if it unavailable instead of check real outbound ip
     */
    public function __construct(
        public VectorInterface  $excludeCountryCodes,
        public ?VectorInterface $exceptOutbounds = null,
        public bool             $force = false
    )
    {
    }
}