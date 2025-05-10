<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache;

use Psr\Clock\ClockInterface;

/**
 * Simple PSR-20 implementation that uses php DateTime.
 *
 * @internal
 */
final class Clock implements ClockInterface
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
