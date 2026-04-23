<?php

declare(strict_types=1);

namespace App\Application\Subscription\UseCase\FetchSubscriptionContent;

use App\Application\Exception\Subscription\FetchSubscriptionContent\UnsupportedSubscriptionContentFormatException;
use App\Application\Subscription\DTO\FetchSubscriptionContent\SubscriptionContentDTO;
use App\Application\Subscription\DTO\FetchSubscriptionContent\SubscriptionContentTypeDTO;
use App\Domain\Shared\Exception\HTTP\HttpException;
use App\Domain\Shared\Ports\Http\HttpPort;
use App\Domain\Shared\Ports\String\Encoding\StringEncodingDetectorPort;
use App\Domain\Subscription\VO\SubscriptionURLVO;
use InvalidArgumentException;
use Psl\Encoding\Base64;
use Psl\Encoding\Exception\IncorrectPaddingException;
use Psl\Encoding\Exception\RangeException;
use RuntimeException;


final readonly class FetchSubscriptionContentUseCase
{

    public function __construct(
        private HttpPort                   $httpPort,
        private StringEncodingDetectorPort $stringEncodingDetectorPort,
    )
    {
    }


    /**
     * Fetch outbounds from provided url
     *
     * @param SubscriptionURLVO $subscriptionUrl Subscription url to fetch outbounds
     *
     * @return SubscriptionContentDTO Fetched subscription content
     *
     * @throws InvalidArgumentException If invalid url provided or invalid response
     * @throws UnsupportedSubscriptionContentFormatException If subscription content format is unsupported
     */
    public function handle(SubscriptionURLVO $subscriptionUrl): SubscriptionContentDTO
    {
        /**
         * Try to load outbounds
         */
        try {
            $rawEncodedSubscriptionContent = $this->httpPort->get(10.0, $subscriptionUrl->getUrl())
                ->getBody()
                ->getContents();
        } catch (RuntimeException) {
            throw new InvalidArgumentException("Unable to read response");
        } catch (HttpException) {
            throw new InvalidArgumentException("Unable to send request");
        }


        /**
         * Try to decode response
         */
        if ($this->stringEncodingDetectorPort->isBase64($rawEncodedSubscriptionContent)) try {
            $rawSubscriptionContent = Base64\decode($rawEncodedSubscriptionContent);
        } catch (IncorrectPaddingException|RangeException) {
            throw new InvalidArgumentException("Invalid response");
        }

        else if ($this->stringEncodingDetectorPort->isUrlEncoded($rawEncodedSubscriptionContent))
            $rawSubscriptionContent = urldecode($rawEncodedSubscriptionContent);

        else $rawSubscriptionContent = $rawEncodedSubscriptionContent;


        /**
         * Get subscription content format
         */
        if ($this->isSchemesFormat($rawSubscriptionContent))
            $subscriptionContentFormat = SubscriptionContentTypeDTO::SCHEMES;

        else if ($this->isConfigFormat($rawSubscriptionContent))
            $subscriptionContentFormat = SubscriptionContentTypeDTO::CONFIG;

        else throw new UnsupportedSubscriptionContentFormatException("Unsupported subscription content format");


        return new SubscriptionContentDTO(
            $rawSubscriptionContent,
            $subscriptionContentFormat,
        );
    }

    private function isSchemesFormat(string $subscriptionContent): bool
    {
        return str_contains($subscriptionContent, '://');
    }

    private function isConfigFormat(string $subscriptionContent): bool
    {
        return json_validate($subscriptionContent);
    }
}