<?php

declare(strict_types=1);

namespace App\Domain\Config\VO;

use App\Domain\Config\Exception\InvalidSchemeIdException;
use Psl\Type\Exception\CoercionException;
use function Psl\Type\non_empty_string;

final readonly class SchemeIdVO
{
    /** @var string $schemeId Scheme id */
    private string $schemeId;


    /**
     * Constructor
     *
     * @param string $schemeId Raw scheme id
     *
     * @throws InvalidSchemeIdException
     */
    public function __construct(string $schemeId)
    {
        /**
         * Check if scheme id is non-empty string
         */
        try {
            $this->schemeId = non_empty_string()->coerce($schemeId);
        } catch (CoercionException) {
            throw new InvalidSchemeIdException();
        }
    }


    /**
     * Get scheme id
     *
     * @return string Scheme id
     */
    public function getSchemeId(): string
    {
        return $this->schemeId;
    }
}