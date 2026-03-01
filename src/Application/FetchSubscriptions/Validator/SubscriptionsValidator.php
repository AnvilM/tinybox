<?php

declare(strict_types=1);

namespace App\Application\FetchSubscriptions\Validator;

use App\Core\Shared\Exception\CriticalException;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Validator;

final readonly class SubscriptionsValidator
{
    private string $schema;

    public function __construct(
        private Validator $validator,
    )
    {
        $this->schema = <<<'JSON'
            {
                "type": "object",
                "patternProperties": {
                    "^.*$": { 
                        "type": "string"
                    }
                },
                "additionalProperties": false
            }
            JSON;
    }

    /**
     * Validate array of subscriptions
     *
     * @param array $subscriptionsArray Unvalidated subscriptions array from file
     *
     * @return void
     *
     * @throws CriticalException Throws if subscriptions array is invalid
     */
    public function validate(array $subscriptionsArray): void
    {
        $validation = $this->validator->validate(
            Helper::toJSON($subscriptionsArray),
            $this->schema
        );

        if ($validation->hasError()) throw new CriticalException(
            "Invalid subscription format",
            $validation->error()->message()
        );
    }
}