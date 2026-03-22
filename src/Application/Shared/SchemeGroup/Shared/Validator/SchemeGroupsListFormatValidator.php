<?php

declare(strict_types=1);

namespace App\Application\Shared\SchemeGroup\Shared\Validator;

use App\Application\Shared\SchemeGroup\Exception\Shared\Validator\InvalidSchemeGroupListFormatException;
use InvalidArgumentException;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Validator;

final readonly class SchemeGroupsListFormatValidator
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
                        "schemes": {
                            "type": "array",
                            "items": {
                                "type": "string"
                            }
                        }
                    },
                    "required": ["name", "schemes"],
                    "additionalProperties": false
                }
            }
            JSON;
    }

    /**
     * Validate array of raw scheme groups
     *
     * @param array $rawSchemeGroupsArray
     *
     * @return void
     *
     * @throws InvalidSchemeGroupListFormatException
     */
    public function validate(array $rawSchemeGroupsArray): void
    {
        try {
            $validation = $this->validator->validate(
                Helper::toJSON($rawSchemeGroupsArray),
                $this->schema
            );
        } catch (InvalidArgumentException) {
            throw new InvalidSchemeGroupListFormatException();
        }

        if ($validation->hasError()) throw new InvalidSchemeGroupListFormatException();
    }
}