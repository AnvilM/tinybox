<?php

declare(strict_types=1);

namespace App\Application\Subscription\UseCase\FetchSubscriptionContent;

use App\Application\Subscription\DTO\UseCase\FetchSubscriptionContent\SubscriptionContentDTO;
use App\Application\Subscription\DTO\UseCase\FetchSubscriptionContent\SubscriptionContentTypeDTO;
use App\Application\Subscription\Exception\UseCase\FetchSubscriptionContent\UnsupportedSubscriptionContentFormatException;
use App\Domain\Shared\Exception\HTTP\HttpException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
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
        private ConfigInstancePort         $configInstancePort
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
        $config = $this->configInstancePort->get()->subscriptionsConfig;

        $headers = ['User-Agent' => $config->useragent];
        if ($config->hwid != null) {
            $headers['X-HWID'] = $config->hwid;
        }

        /**
         * Try to load outbounds
         */
        try {
            $response = $this->httpPort->get((float)$config->timeout, $subscriptionUrl->getUrl(), $headers);

            /**
             * Redirect is returned as is only if following redirects is disabled
             */
            if ($response->status >= 300 && $response->status < 400) {
                $location = $response->headers->get('location');

                throw new InvalidArgumentException(
                    "Server responded with redirect (HTTP {$response->status})"
                    . ($location !== null ? " to $location" : '')
                    . ", following redirects is disabled"
                );
            }

            $rawEncodedSubscriptionContent = $response->body?->readAll();
        } catch (RuntimeException) {
            throw new InvalidArgumentException("Unable to read response");
        } catch (HttpException $e) {
            throw new InvalidArgumentException($e->getMessage() !== '' ? $e->getMessage() : "Unable to send request");
        }


        /**
         * Try to decode response
         *
         * Plain text with schemes (possibly preceded by a header/comments like "# profile-title: ...")
         * must not be decoded as a whole, otherwise percent-encoded tags would be corrupted.
         */
        $rawEncodedSubscriptionContent = trim((string)$rawEncodedSubscriptionContent);

        if ($this->hasSchemeLine($rawEncodedSubscriptionContent))
            $rawSubscriptionContent = $this->stripPreamble($rawEncodedSubscriptionContent);

        else if ($this->stringEncodingDetectorPort->isBase64($rawEncodedSubscriptionContent)) try {
            $rawSubscriptionContent = $this->stripPreamble(Base64\decode($rawEncodedSubscriptionContent));
        } catch (IncorrectPaddingException|RangeException) {
            throw new InvalidArgumentException("Invalid response");
        }

        else if ($this->stringEncodingDetectorPort->isUrlEncoded($rawEncodedSubscriptionContent))
            $rawSubscriptionContent = $this->stripPreamble(urldecode($rawEncodedSubscriptionContent));

        else $rawSubscriptionContent = $this->stripPreamble($rawEncodedSubscriptionContent);


        /**
         * Get subscription content format
         */
        if ($this->isConfigFormat($rawSubscriptionContent))
            $subscriptionContentFormat = SubscriptionContentTypeDTO::CONFIG;

        else if ($this->isSchemesFormat($rawSubscriptionContent))
            $subscriptionContentFormat = SubscriptionContentTypeDTO::SCHEMES;

        else throw new UnsupportedSubscriptionContentFormatException($rawSubscriptionContent);


        return new SubscriptionContentDTO(
            $rawSubscriptionContent,
            $subscriptionContentFormat,
        );
    }

    private const SCHEME_LINE_REGEX = '~^[a-z][a-z0-9+.\-]*://~im';

    private function hasSchemeLine(string $content): bool
    {
        return !json_validate($content) && preg_match(self::SCHEME_LINE_REGEX, $content) === 1;
    }

    /**
     * Remove any text (comments, headers) placed before the first scheme line
     */
    private function stripPreamble(string $content): string
    {
        if (json_validate($content)) return $content;

        if (preg_match(self::SCHEME_LINE_REGEX, $content, $m, PREG_OFFSET_CAPTURE) !== 1) return $content;

        return str_replace("\r", '', substr($content, $m[0][1]));
    }

    private function isConfigFormat(string $subscriptionContent): bool
    {
        return json_validate($subscriptionContent);
    }

    private function isSchemesFormat(string $subscriptionContent): bool
    {
        return str_contains($subscriptionContent, '://');
    }
}