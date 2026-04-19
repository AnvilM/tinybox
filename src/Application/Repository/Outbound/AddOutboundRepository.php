<?php

declare(strict_types=1);

namespace App\Application\Repository\Outbound;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\Outbound\Shared\File\ReadOutbounds;
use App\Application\Repository\Outbound\Shared\File\WriteOutbounds;
use App\Application\Repository\Outbound\Shared\OutboundRepository;
use App\Application\Repository\Outbound\Shared\Validator\OutboundsListFormatValidator;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Factory\FromRawOutbound\FromRawOutboundFactory;
use App\Domain\Shared\Ports\Outbound\Parser\RawOutboundParserPort;

final class AddOutboundRepository extends OutboundRepository
{
    public function __construct(ReadOutbounds $readOutbounds, OutboundsListFormatValidator $outboundsListFormatValidator, WriteOutbounds $writeOutbounds, RawOutboundParserPort $rawOutboundParserPort, FromRawOutboundFactory $fromRawOutboundFactory)
    {
        parent::__construct($readOutbounds, $outboundsListFormatValidator, $writeOutbounds, $rawOutboundParserPort, $fromRawOutboundFactory);
    }

    /**
     * Add outbound to repository
     *
     * NOTE: Method doesn't write outbounds list to file. Use method save
     *
     * @param Outbound $outbound Outbound to save
     *
     * @return self Current AddOutbound object
     *
     * @throws UnableToGetListException If unable to add outbound e.g. unable to write file or read
     * @throws OutboundAlreadyExistsException If provided outbound already exists in outbounds map
     */
    public function add(Outbound $outbound): self
    {
        /**
         * Get all outbounds list
         */
        $outbounds = $this->getOutboundsList();


        /**
         * Add outbound to repository
         */
        $outbounds->add($outbound);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function save(): OutboundMap
    {
        return parent::save();
    }
}