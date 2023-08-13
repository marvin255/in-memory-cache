<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache;

/**
 * Object that provides timestamp.
 */
interface Timer
{
    /**
     * Return current timestamp.
     */
    public function getCurrentTimestamp(): int;
}
