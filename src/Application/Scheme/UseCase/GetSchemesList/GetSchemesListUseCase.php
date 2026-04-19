<?php

declare(strict_types=1);

namespace App\Application\Scheme\UseCase\GetSchemesList;

use App\Application\Repository\Outbound\AddOutboundRepository;
use App\Application\Repository\Outbound\GetOutboundsListRepository;
use App\Application\Repository\Scheme\GetSchemesListRepository;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Factory\FromScheme\FromSchemeOutboundFactory;
use App\Domain\Shared\Exception\CriticalException;

final readonly class GetSchemesListUseCase
{
    public function __construct(
        private GetSchemesListRepository   $getSchemesListRepository,
        private AddOutboundRepository      $addOutboundRepository,
        private GetOutboundsListRepository $getOutboundsListRepository,

    )
    {
    }


    /**
     * @throws CriticalException
     */
    public function handle(): void
    {
        foreach ($this->getSchemesListRepository->getSchemesList()->getMap() as $scheme) {
            try {
                $this->addOutboundRepository->add(
                    FromSchemeOutboundFactory::fromScheme($scheme, $this->getOutboundsListRepository->getOutboundsList()->count()),
                );
            } catch (OutboundAlreadyExistsException $outboundAlreadyExistsException) {
                continue;
            }
        }

        $this->addOutboundRepository->save();
    }
}