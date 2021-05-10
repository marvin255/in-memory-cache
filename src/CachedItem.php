<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache;

/**
 * DTO to store item in the cache.
 */
class CachedItem
{
    /**
     * @var mixed
     */
    public $payload = null;

    public int $validTill = 0;

    public int $selectCount = 0;

    /**
     * @param mixed $payload
     * @param int   $validTill
     */
    public function __construct($payload, int $validTill)
    {
        $this->payload = $payload;
        $this->validTill = $validTill;
    }

    /**
     * Returns true if item still valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->validTill >= time();
    }
}
