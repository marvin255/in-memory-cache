<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache\Tests;

use Marvin255\InMemoryCache\Clock;

/**
 * @internal
 */
class ClockTest extends BaseCase
{
    public function testNow(): void
    {
        $clock = new Clock();
        $res = $clock->now();

        $this->assertInstanceOf(\DateTimeImmutable::class, $res);
    }
}
