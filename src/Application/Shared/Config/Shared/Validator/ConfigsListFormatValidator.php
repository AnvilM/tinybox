<?php

declare(strict_types=1);

namespace App\Application\Shared\Config\Shared\Validator;

use App\Application\Shared\Config\Exception\Shared\Validator\InvalidConfigsListFormatException;
use InvalidArgumentException;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Validator;

final readonly class ConfigsListFormatValidator
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
     * Validate array of raw configs
     *
     * @param array $rawConfigsArray
     *
     * @return void
     *
     * @throws InvalidConfigsListFormatException
     */
    public function validate(array $rawConfigsArray): void
    {
        try {
            $validation = $this->validator->validate(
                Helper::toJSON($rawConfigsArray),
                $this->schema
            );
        } catch (InvalidArgumentException) {
            throw new InvalidConfigsListFormatException();
        }

        if ($validation->hasError()) throw new InvalidConfigsListFormatException();
    }
}