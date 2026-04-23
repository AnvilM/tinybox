<?php

declare(strict_types=1);

namespace App\Application\Repository\Subscription\Shared\Validator;

use App\Application\Exception\Repository\Subscription\Validator\InvalidSubscriptionsListFormatException;
use InvalidArgumentException;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Validator;

final readonly class SubscriptionsListFormatValidator
{
    private string $schema;

    public function __construct(
        private Validator $validator,
    )
    {
        $this->schema = <<<'JSON'
            {
                "type": "array",
                "items": {
                    "type": "object",
                    "properties": {
                        "name": {
                            "type": "string"
                        },
                        "url": {
                            "type": "string",
                            "format": "uri"
                        },
                        "outbounds": {
                            "type": "array",
                            "items": {
                                "type": "string"
                            }
                        }
                    },
                    "required": ["name", "url", "outbounds"],
                    "additionalProperties": false
                }
            }
            JSON;
    }

    /**
     * Validate array of raw subscriptions
     *
     * @param array $rawSubscriptionsArray
     *
     * @return void
     *
     * @throws InvalidSubscriptionsListFormatException
     */
    public function validate(array $rawSubscriptionsArray): void
    {
        try {
            $validation = $this->validator->validate(
                Helper::toJSON($rawSubscriptionsArray),
                $this->schema
            );
        } catch (InvalidArgumentException) {
            throw new InvalidSubscriptionsListFormatException();
        }

        if ($validation->hasError()) throw new InvalidSubscriptionsListFormatException();
    }
}