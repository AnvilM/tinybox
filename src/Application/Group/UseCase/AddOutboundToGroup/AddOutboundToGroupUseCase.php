<?php

declare(strict_types=1);

namespace App\Application\Group\UseCase\AddOutboundToGroup;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Exception\Repository\Shared\UnableToSaveListException;
use App\Application\Repository\Group\AddOutboundToGroupOrCreateNewRepository;
use App\Application\Repository\Group\SaveGroupListRepository;
use App\Application\Repository\Outbound\GetOutboundsListRepository;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Exception\OutboundNotFoundException;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use InvalidArgumentException;

final readonly class AddOutboundToGroupUseCase
{
    public function __construct(
        private GetOutboundsListRepository              $getOutboundsList,
        private AddOutboundToGroupOrCreateNewRepository $addOutboundToGroupOrCreateNewRepository,
        private SaveGroupListRepository                 $saveGroupListRepository,
    )
    {
    }

    /**
     * Add outbound to group or create new group with provided outbound
     *
     * @throws CriticalException
     */
    public function handle(string $groupName, int $outboundId): void
    {

        /**
         * Try to read outbounds list
         */
        try {
            $outbounds = $this->getOutboundsList->getOutboundsList();
        } catch (UnableToGetListException $e) {
            throw new CriticalException($e->getMessage(), $e->getDebugMessage());
        }


        /**
         * Try to get outbound with provided id
         */
        try {
            $outbound = $outbounds->getWithId($outboundId);
        } catch (OutboundNotFoundException $e) {
            throw new CriticalException("Outbound with id $outboundId does not exist", $e->getDebugMessage());
        }


        /**
         * Try to create group name
         */
        try {
            $groupName = new NonEmptyStringVO($groupName);
        } catch (InvalidArgumentException) {
            throw new CriticalException("Invalid group name provided", $groupName);
        }


        /**
         * Try to add outbound to group or create new
         * group list with provided outbound and save groups list
         */
        try {
            $this->addOutboundToGroupOrCreateNewRepository->add($groupName, $outbound);
            $this->saveGroupListRepository->save();
        } catch (UnableToGetListException|OutboundAlreadyExistsException|UnableToSaveListException $e) {
            throw new CriticalException(match (true) {
                $e instanceof OutboundAlreadyExistsException => "Outbound with id $outboundId already exists in group with name {$groupName->getValue()}",
                $e instanceof UnableToGetListException => "Unable to get subscriptions list",
                $e instanceof UnableToSaveListException => "Unable to add outbound"
            }, $e->getDebugMessage());
        }
    }
}