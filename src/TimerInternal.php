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
    public function __construct(
        private readonly ?int $freezeTimeAt = null
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentTimestamp(): int
    {
        if ($this->freezeTimeAt !== null) {
            return $this->freezeTimeAt;
        }

        return time();
    }
}
