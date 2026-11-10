<?php

declare(strict_types=1);

namespace App\Application\Subscription\UseCase\CreateOutboundsFromSchemes;

use App\Application\Outbound\Exception\UnableToParseRawSchemeStringException;
use App\Application\Outbound\UseCase\CreateOutboundFromScheme\CreateOutboundFromSchemeUseCase;
use App\Application\Subscription\Exception\UseCase\SaveFetchedSubscriptionSchemes\NoValidSchemesFoundException;
use App\Domain\Outbound\Collection\UniqueTagOutboundsMap;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Exception\UnsupportedProtocolException;
use App\Domain\Outbound\Exception\UnsupportedSecurityException;
use App\Domain\Outbound\Exception\UnsupportedTransportException;
use App\Domain\Shared\Ports\IO\Reporter\ReporterInstancePort;
use App\Domain\Shared\ReporterEvent\ReporterEventBuilder;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventAttachmentVO;
use InvalidArgumentException;

final readonly class CreateOutboundsFromSchemesUseCase
{
    public function __construct(
        private CreateOutboundFromSchemeUseCase $createOutboundFromSchemeUseCase,
        private ReporterInstancePort            $reporterInstancePort,
    )
    {
    }


    /**
     * Parse fetched schemes into a map of outbounds, nothing is saved
     *
     * Rules applied to the parsed outbounds:
     *  - outbounds with the same content (see {@see Outbound::equalsContent()}) are kept once, whatever their tags are.
     *    The comparison is strict: if any parameter differs (e.g. TLS fingerprint), outbounds are different;
     *  - outbounds with different content but the same tag are all kept: tags must be unique
     *    (sing-box refuses duplicated tags), so the next ones get a numeric suffix: "tag #2", "tag #3", ...
     *
     * @param string $schemesString Schemes as plain text, one scheme per line
     *
     * @return UniqueTagOutboundsMap Not empty map of outbounds
     *
     * @throws NoValidSchemesFoundException If valid schemes not found
     */
    public function handle(string $schemesString): UniqueTagOutboundsMap
    {
        /**
         * Explode raw schemes string by \n
         */
        $schemesStrings = explode("\n", $schemesString);


        /**
         * Create empty unique outbounds map
         */
        $outbounds = new UniqueTagOutboundsMap();


        foreach ($schemesStrings as $schemeString) {
            /**
             * Try to create outbound entity from string and add it to outbound map
             */
            try {
                $outbound = $this->createOutboundFromSchemeUseCase->handle($schemeString);

                /**
                 * Skip outbound with the same content as already added one, even if tags differ
                 */
                $duplicate = $outbounds->getContentDuplicate($outbound);

                if ($duplicate instanceof Outbound) {
                    $this->reporterInstancePort->get()->notify(
                        ReporterEventBuilder::warning("Duplicate: Outbound {$outbound->getTagString()} has the same content as {$duplicate->getTagString()} already exists in subscription")
                            ->attachments(ReporterEventAttachmentVO::veryVerbose('Scheme: ' . $schemeString))->normal()
                    );

                    continue;
                }

                /**
                 * Keep outbound with another content but the same tag under a unique tag
                 */
                $uniqueTag = $outbounds->getUniqueTag($outbound->getTagString());

                if ($uniqueTag !== $outbound->getTagString()) {
                    $this->reporterInstancePort->get()->notify(
                        ReporterEventBuilder::warning("Tag {$outbound->getTagString()} is already used by an outbound with another content, outbound renamed to {$uniqueTag}")
                            ->attachments(ReporterEventAttachmentVO::veryVerbose('Scheme: ' . $schemeString))->verbose()
                    );

                    $outbound = $outbound->withTag($uniqueTag);
                }

                $outbounds->add($outbound);
            } catch (UnableToParseRawSchemeStringException|UnsupportedProtocolException|UnsupportedSecurityException|UnsupportedTransportException|InvalidArgumentException $e) {
                $this->reporterInstancePort->get()->notify(
                    ReporterEventBuilder::warning("Unable to create outbound from scheme string" . ($e->getMessage() != '' ? (': ' . $e->getMessage()) : ''))
                        ->attachments(ReporterEventAttachmentVO::veryVerbose('Scheme: ' . $schemeString))->normal(),
                );
            } catch (OutboundAlreadyExistsException $e) {
                $this->reporterInstancePort->get()->notify(
                    ReporterEventBuilder::warning("Duplicate: Outbound {$e->outbound->getTagString()} already exists in subscription")
                        ->attachments(ReporterEventAttachmentVO::veryVerbose('Scheme: ' . $schemeString))->normal()
                );
            }
        }


        /**
         * Check if outbounds map is not empty
         */
        if ($outbounds->getMap()->isEmpty()) throw new NoValidSchemesFoundException('No valid schemes found');

        return $outbounds;
    }
}
