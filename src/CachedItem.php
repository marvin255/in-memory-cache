<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache;

/**
 * DTO to store item in the cache.
 *
 * @internal
 */
final class CachedItem
{
    private int $selectCount = 0;

    public function __construct(
        private readonly mixed $payload,
        private readonly int $validTill,
    ) {
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
