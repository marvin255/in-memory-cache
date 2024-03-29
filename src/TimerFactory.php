<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache;

/**
 * Factory that provides timer objects.
 *
 * @psalm-api
 */
final class TimerFactory
{
    private static ?Timer $timer = null;

    private function __construct()
    {
    }

    /**
     * Create and return a timer object.
     */
    public static function create(): Timer
    {
        if (self::$timer === null) {
            self::$timer = new TimerInternal();
        }

        return self::$timer;
    }
}
