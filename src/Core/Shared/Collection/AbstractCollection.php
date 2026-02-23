<?php

declare(strict_types=1);

namespace App\Core\Shared\Collection;

use InvalidArgumentException;
use Ramsey\Collection\AbstractCollection as BaseCollection;

/**
 * @template T
 *
 * @extends BaseCollection<T>
 */
abstract class AbstractCollection extends BaseCollection
{
    private function __construct(array $data)
    {
        if (empty($data)) throw new InvalidArgumentException(static::class . " Can not be empty");

        parent::__construct($data);
    }

    /**
     * Creates new collection
     *
     * @param array $data Data to create collection
     *
     * @return static
     *
     * @throws InvalidArgumentException
     */

    public static function create(array $data): static
    {
        return new static($data);
    }
}