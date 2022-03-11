<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache;

/**
 * DTO to store item in the cache.
 */
class CachedItem
{
    private mixed $payload;

    private int $validTill;

    private int $selectCount = 0;

    public function __construct(mixed $payload, int $validTill)
    {
        $this->payload = $payload;
        $this->validTill = $validTill;
    }

    public function getValidTill(): int
    {
        return $this->validTill;
    }

    public function getSelectCount(): int
    {
        return $this->selectCount;
    }

    public function getPayload(): mixed
    {
        ++$this->selectCount;

        return $this->payload;
    }
}
