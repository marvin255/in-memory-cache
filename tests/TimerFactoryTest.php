<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache\Tests;

use Marvin255\InMemoryCache\Timer;
use Marvin255\InMemoryCache\TimerFactory;

/**
 * @internal
 */
class TimerFactoryTest extends BaseCase
{
    public function testCreate(): void
    {
        $res = TimerFactory::create();

        $this->assertInstanceOf(Timer::class, $res);
    }
}
