<?php

declare(strict_types=1);

namespace App\Application\Subscription\UseCase\SaveFetchedSubscriptionSchemes;

use App\Application\Outbound\Exception\UnableToParseRawSchemeStringException;
use App\Application\Outbound\UseCase\CreateOutboundFromScheme\CreateOutboundFromSchemeUseCase;
use App\Application\Repository\Outbound\AddOutboundRepository;
use App\Application\Repository\Outbound\GetOutboundsListRepository;
use App\Application\Repository\Shared\Exception\UnableToGetListException;
use App\Application\Repository\Shared\Exception\UnableToSaveListException;
use App\Application\Repository\Subscription\AddSubscriptionRepository;
use App\Application\Subscription\Exception\UseCase\SaveFetchedSubscriptionSchemes\NoValidSchemesFoundException;
use App\Domain\Outbound\Collection\UniqueTagAndContentOutboundsMap;
use App\Domain\Outbound\Collection\UniqueTagOutboundsMap;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Exception\UnsupportedProtocolException;
use App\Domain\Outbound\Exception\UnsupportedSecurityException;
use App\Domain\Outbound\Exception\UnsupportedTransportException;
use App\Domain\Shared\Ports\IO\Reporter\ReporterInstancePort;
use App\Domain\Shared\ReporterEvent\ReporterEventBuilder;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventAttachmentVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Subscription\Entity\OutboundsSubscription;
use App\Domain\Subscription\Exception\SubscriptionAlreadyExistsException;
use App\Domain\Subscription\VO\SubscriptionURLVO;
use InvalidArgumentException;

final readonly class SaveFetchedSubscriptionSchemesUseCase
{
    public function __construct(
        private CreateOutboundFromSchemeUseCase $createOutboundFromSchemeUseCase,
        private AddOutboundRepository           $addOutboundRepository,
        private AddSubscriptionRepository       $addSubscriptionRepository,
        private GetOutboundsListRepository      $getOutboundsListRepository,
        private ReporterInstancePort            $reporterInstancePort,
    )
    {
    }


    /**
     * Parse and save fetched subscription schemes and subscription
     *
     * @param NonEmptyStringVO $subscriptionName Subscription name
     * @param SubscriptionURLVO $subscriptionUrl Subscription Url
     * @param string $fetchedSchemesString Fetched schemes as plain text
     *
     * @throws NoValidSchemesFoundException If valid schemes not found
     * @throws SubscriptionAlreadyExistsException If subscription with provided name or url already exist
     * @throws UnableToGetListException If unable to get list of subscriptions or outbounds
     * @throws UnableToSaveListException If unable to save subscriptions list or outbounds list
     */
    public function handle(NonEmptyStringVO $subscriptionName, SubscriptionURLVO $subscriptionUrl, string $fetchedSchemesString, bool $skipDuplicates): void
    {

        /**
         * Explode raw schemes string by \n
         */
        $schemesStrings = explode("\n", $fetchedSchemesString);


        /**
         * Create empty unique outbounds map
         */
        $outbounds = $skipDuplicates ? new UniqueTagAndContentOutboundsMap() : new UniqueTagOutboundsMap();


        foreach ($schemesStrings as $schemeString) {
            /**
             * Try to create outbound entity from string and add it to outbound mao
             */
            try {
                $outbounds->add(
                    $this->createOutboundFromSchemeUseCase->handle($schemeString)
                );
            } catch (UnableToParseRawSchemeStringException|UnsupportedProtocolException|UnsupportedSecurityException|UnsupportedTransportException|InvalidArgumentException $e) {
                $this->reporterInstancePort->get()->notify(
                    ReporterEventBuilder::warning("Unable to create outbound from scheme string" . ($e->getMessage() != '' ? (': ' . $e->getMessage()) : ''))
                        ->attachments(ReporterEventAttachmentVO::debug('Scheme: ' . $schemeString))->normal(),
                );
            } catch (OutboundAlreadyExistsException $e) {
                $this->reporterInstancePort->get()->notify(
                    ReporterEventBuilder::warning("Duplicate: Outbound {$e->outbound->getTagString()} already exists in subscription")
                        ->attachments(ReporterEventAttachmentVO::debug('Scheme: ' . $schemeString))->normal()
                );
            }
        }


        /**
         * Check if outbounds map is not empty
         */
        if ($outbounds->getMap()->isEmpty()) throw new NoValidSchemesFoundException('No valid schemes found');


        foreach ($outbounds->getOutbounds() as $outbound) {

            /**
             * Find duplicate of each fetched outbounds
             */
            $duplicate = $this->getOutboundsListRepository->getOutboundsList()->getDuplicate($outbound);


            /**
             * Remove duplicate from fetched outbounds and add existed outbound
             */
            if ($duplicate instanceof Outbound) $outbounds
                ->remove($outbound)
                ->add($duplicate);


            /**
             * Add outbound to outbounds list if duplicates not found
             */
            else $this->addOutboundRepository->add($outbound);
        }


        /**
         * Add new subscription and save subscriptions list
         */
        $this->addSubscriptionRepository->add(new OutboundsSubscription(
            $subscriptionName,
            $subscriptionUrl,
            $outbounds
        ))->save();


        /**
         * Save outbounds list
         */
        $this->addOutboundRepository->save();
    }
}