<?php

declare(strict_types=1);

namespace App\Application\Repository\Outbound\Shared\Validator;

use App\Application\Exception\Repository\Outbound\Validator\InvalidOutboundsListFormatException;
use InvalidArgumentException;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Validator;

final readonly class OutboundsListFormatValidator
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
                    "type": "object"
                }
            }
            JSON;
    }

    /**
     * Validate array of raw outbounds strings
     *
     * @param string[] $rawOutboundsArray
     *
     * @return void
     *
     * @throws InvalidOutboundsListFormatException
     */
    public function validate(array $rawOutboundsArray): void
    {
        try {
            $validation = $this->validator->validate(
                Helper::toJSON($rawOutboundsArray),
                $this->schema
            );
        } catch (InvalidArgumentException) {
            throw new InvalidOutboundsListFormatException();
        }

        if ($validation->hasError()) throw new InvalidOutboundsListFormatException();
    }
}