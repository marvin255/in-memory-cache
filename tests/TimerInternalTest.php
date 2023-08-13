<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache\Tests;

use Marvin255\InMemoryCache\TimerInternal;

/**
 * @internal
 */
class TimerInternalTest extends BaseCase
{
    public function testGetCurrentTimestamp(): void
    {
        $time = time();

        $timer = new TimerInternal();
        $res = $timer->getCurrentTimestamp();

        $this->assertSame($time, $res);
    }

    public function testGetCurrentTimestampWithFrozenTime(): void
    {
        $time = 123;

        $timer = new TimerInternal($time);
        $res = $timer->getCurrentTimestamp();

        $this->assertSame($time, $res);
    }
}
