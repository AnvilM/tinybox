<?php

declare(strict_types=1);

namespace App\Application\Shared\Scheme\Shared\Validator;

use App\Application\Shared\Scheme\Exception\Shared\Validator\InvalidSchemesListFormatException;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Validator;

final readonly class SchemesListFormatValidator
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
                    "type": "string"
                }
            }
            JSON;
    }

    /**
     * Validate array of raw schemes strings
     *
     * @param string[] $rawSchemesArray
     *
     * @return void
     *
     * @throws InvalidSchemesListFormatException
     */
    public function validate(array $rawSchemesArray): void
    {
        $validation = $this->validator->validate(
            Helper::toJSON($rawSchemesArray),
            $this->schema
        );

        if ($validation->hasError()) throw new InvalidSchemesListFormatException();
    }
}