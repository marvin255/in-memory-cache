<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache;

/**
 * Factory that provides timer object.
 */
final class TimerFactory
{
    private static ?Timer $timer = null;

    private function __construct()
    {
    }

    /**
     * Return timer object.
     */
    public static function create(): Timer
    {
        if (self::$timer === null) {
            self::$timer = new TimerInternal();
        }

        return self::$timer;
    }
}
