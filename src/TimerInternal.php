<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache;

/**
 * Object that provides timestamp using php time() function.
 *
 * @internal
 */
final class TimerInternal implements Timer
{
    /**
     * {@inheritdoc}
     */
    public function getCurrentTimestamp(): int
    {
        return time();
    }
}
