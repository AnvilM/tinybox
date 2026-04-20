<?php

declare(strict_types=1);

namespace App\Application\Repository\Group\Shared\Validator;

use App\Application\Exception\Repository\Group\Validator\InvalidGroupListFormatException;
use InvalidArgumentException;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Validator;

final readonly class GroupsListFormatValidator
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
                        "outbounds": {
                            "type": "array",
                            "items": {
                                "type": "integer"
                            }
                        }
                    },
                    "required": ["name", "outbounds"],
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
     * @throws InvalidGroupListFormatException
     */
    public function validate(array $rawSchemeGroupsArray): void
    {
        try {
            $validation = $this->validator->validate(
                Helper::toJSON($rawSchemeGroupsArray),
                $this->schema
            );
        } catch (InvalidArgumentException) {
            throw new InvalidGroupListFormatException();
        }

        if ($validation->hasError()) throw new InvalidGroupListFormatException();
    }
}