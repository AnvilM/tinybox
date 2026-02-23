<?php

declare(strict_types=1);

namespace App\Core\Services\SubscriptionService\LoadSubscriptionList\Validation;

use App\Core\Exceptions\ApplicationException;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Validator;

final readonly class ValidateSubscriptionList
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
     * @param array $appConfiguration
     * @throws ApplicationException
     */
    public function validateSubscriptionList(array $appConfiguration): void
    {
        $validation = $this->validator->validate(
            Helper::toJSON($appConfiguration),
            $this->schema
        );
        
        if($validation->hasError()) throw new ApplicationException(
            "Invalid subscription format",
        );
    }
}