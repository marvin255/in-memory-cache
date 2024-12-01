<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache\Tests;

use Marvin255\InMemoryCache\InMemoryCache;
use Marvin255\InMemoryCache\InvalidArgumentException;
use Psr\Clock\ClockInterface;

/**
 * @internal
 */
class InMemoryCacheTest extends BaseCase
{
    public function testConstructStackSizeException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new InMemoryCache(0, 1);
    }

    public function testConstructDefaultTTLException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new InMemoryCache(1, 0);
    }

    public function testGet(): void
    {
        $key = 'test';
        $value = 'test value';
        $ttl = 60;

        $cache = new InMemoryCache(1, 1);
        $cache->set($key, $value, $ttl);
        $res = $cache->get($key);

        $this->assertSame($value, $res);
    }

    public function testGetObject(): void
    {
        $key = 'test';
        $value = new \stdClass();
        $value->test = 'test value';
        $ttl = 60;

        $cache = new InMemoryCache();
        $cache->set($key, $value, $ttl);
        $res = $cache->get($key);

        $this->assertSame($value, $res);
    }

    public function testGetDefault(): void
    {
        $key = 'test';
        $default = 'default';

        $cache = new InMemoryCache();
        $res = $cache->get($key, $default);

        $this->assertSame($default, $res);
    }

    public function testGetAfterTtl(): void
    {
        $key = 'test';
        $value = 'test value';
        $ttl = 1;
        $timestamp = 123;

        $clockMock = $this->createClockMock(
            [
                $timestamp,
                $timestamp + 2,
            ]
        );

        $cache = new InMemoryCache(clock: $clockMock);
        $cache->set($key, $value, $ttl);
        $res = $cache->get($key, null);

        $this->assertNull($res);
    }

    public function testGetRigntInTheEndOfTtl(): void
    {
        $key = 'test';
        $value = 'test value';
        $ttl = 1;
        $timestamp = 123;

        $clockMock = $this->createClockMock(
            [
                $timestamp,
                $timestamp + 1,
            ]
        );

        $cache = new InMemoryCache(clock: $clockMock);
        $cache->set($key, $value, $ttl);
        $res = $cache->get($key, null);

        $this->assertSame($res, $value);
    }

    public function testGetDateInterval(): void
    {
        $key = 'test';
        $value = 'test value';
        $ttl = new \DateInterval('PT10S');

        $cache = new InMemoryCache();
        $cache->set($key, $value, $ttl);
        $res = $cache->get($key);

        $this->assertSame($value, $res);
    }

    public function testGetAfterDateInterval(): void
    {
        $key = 'test';
        $value = 'test value';
        $ttl = new \DateInterval('PT1S');
        $timestamp = 123;

        $clockMock = $this->createClockMock(
            [
                $timestamp,
                $timestamp + 2,
            ]
        );

        $cache = new InMemoryCache(clock: $clockMock);
        $cache->set($key, $value, $ttl);
        $res = $cache->get($key, null);

        $this->assertNull($res);
    }

    public function testSetReturnsTrue(): void
    {
        $key = 'test';
        $value = 'test value';
        $ttl = 10;

        $cache = new InMemoryCache();
        $res = $cache->set($key, $value, $ttl);

        $this->assertTrue($res);
    }

    public function testSetMultiple(): void
    {
        $key = 'test';
        $value = 'test value';
        $key1 = 'test_1';
        $value1 = 'test value 1';
        $ttl = 60;

        $cache = new InMemoryCache();
        $res = $cache->setMultiple(
            [
                $key => $value,
                $key1 => $value1,
            ],
            $ttl
        );

        $this->assertTrue(
            $res,
            'value returned by method must be true'
        );
        $this->assertSame(
            $value,
            $cache->get($key),
            'first cache item must be saved'
        );
        $this->assertSame(
            $value1,
            $cache->get($key1),
            'second cache item must be saved'
        );
    }

    public function testSetMultipleIntegerKey(): void
    {
        $key = 'test';
        $value = 'test value';
        $key1 = 10;
        $value1 = 'test value 1';
        $ttl = 60;

        $cache = new InMemoryCache();
        $res = $cache->setMultiple(
            [
                $key => $value,
                $key1 => $value1,
            ],
            $ttl
        );

        $this->assertTrue(
            $res,
            'value returned by method must be true'
        );
        $this->assertSame(
            $value,
            $cache->get($key),
            'first cache item must be saved'
        );
        $this->assertSame(
            $value1,
            $cache->get((string) $key1),
            'second cache item must be saved'
        );
    }

    public function testGetMultiple(): void
    {
        $key = 'test';
        $value = 'test value';
        $key1 = 'test_1';
        $value1 = 'test value 1';
        $key2 = 'test_2';
        $ttl = 60;
        $default = 'default';

        $cache = new InMemoryCache();
        $cache->set($key, $value, $ttl);
        $cache->set($key1, $value1, $ttl);
        $res = $cache->getMultiple(
            [
                $key2,
                $key1,
                $key,
            ],
            $default
        );

        $this->assertSame(
            [
                $key2 => $default,
                $key1 => $value1,
                $key => $value,
            ],
            $res
        );
    }

    public function testHas(): void
    {
        $key = 'test';
        $value = 'test value';
        $ttl = 60;

        $cache = new InMemoryCache();
        $cache->set($key, $value, $ttl);
        $res = $cache->has($key);

        $this->assertTrue($res);
    }

    public function testDoesNotHave(): void
    {
        $key = 'test';
        $value = 'test value';
        $ttl = 60;

        $cache = new InMemoryCache();
        $cache->set($key, $value, $ttl);
        $res = $cache->has('unexisted');

        $this->assertFalse($res);
    }

    public function testHasExpired(): void
    {
        $key = 'test';
        $value = 'test value';
        $ttl = 1;
        $timestamp = 123;

        $clockMock = $this->createClockMock(
            [
                $timestamp,
                $timestamp + 2,
            ]
        );

        $cache = new InMemoryCache(clock: $clockMock);
        $cache->set($key, $value, $ttl);
        $res = $cache->has($key);

        $this->assertFalse($res);
    }

    public function testDelete(): void
    {
        $key = 'test';
        $value = 'test value';
        $ttl = 60;

        $cache = new InMemoryCache();
        $cache->set($key, $value, $ttl);
        $res = $cache->delete($key);

        $this->assertTrue(
            $res,
            'delete method must return true'
        );
        $this->assertFalse(
            $cache->has($key),
            'has method must return false after deleting'
        );
    }

    public function testDeleteMultiple(): void
    {
        $key = 'test';
        $value = 'test value';
        $key1 = 'test_1';
        $value1 = 'test value 1';
        $key2 = 'test_2';
        $value2 = 'test value 2';
        $ttl = 60;

        $cache = new InMemoryCache();
        $cache->set($key, $value, $ttl);
        $cache->set($key1, $value1, $ttl);
        $cache->set($key2, $value2, $ttl);
        $res = $cache->deleteMultiple(
            [
                $key2,
                $key,
            ]
        );

        $this->assertTrue(
            $res,
            'deleteMultiple method must return true'
        );
        $this->assertFalse(
            $cache->has($key),
            'has method for the firts item must return false after deleting'
        );
        $this->assertSame(
            $value1,
            $cache->get($key1),
            'deleteMultiple mustn\'t remove keys thet were not set'
        );
        $this->assertFalse(
            $cache->has($key2),
            'has method for the second item must return false after deleting'
        );
    }

    public function testClear(): void
    {
        $key = 'test';
        $value = 'test value';
        $key1 = 'test_1';
        $value1 = 'test value 1';
        $ttl = 60;

        $cache = new InMemoryCache();
        $cache->set($key, $value, $ttl);
        $cache->set($key1, $value1, $ttl);
        $res = $cache->clear();

        $this->assertFalse($cache->has($key));
        $this->assertFalse($cache->has($key1));
        $this->assertTrue($res);
    }

    public function testStackSizeOverflow(): void
    {
        $key = 'test';
        $value = 'test value';
        $key1 = 'test_1';
        $value1 = 'test value 1';
        $key2 = 'test_2';
        $value2 = 'test value 2';

        $cache = new InMemoryCache(2, 60);
        $cache->set($key, $value);
        $cache->set($key1, $value1);
        $cache->get($key1);
        $cache->set($key2, $value2);

        $this->assertFalse(
            $cache->has($key),
            'item that has the smallest counter must be removed'
        );
        $this->assertTrue(
            $cache->has($key1),
            'item with the bigger counter must be saved'
        );
        $this->assertTrue(
            $cache->has($key2),
            'new item must be added'
        );
    }

    public function testStackSizeTTL(): void
    {
        $key = 'test';
        $value = 'test value';
        $key1 = 'test_1';
        $value1 = 'test value 1';
        $key2 = 'test_2';
        $value2 = 'test value 2';
        $timestamp = 123;
        $nextTimestamp = 125;

        $clockMock = $this->createClockMock(
            [
                $timestamp,
                $timestamp,
                $nextTimestamp,
                $nextTimestamp,
                $nextTimestamp,
                $nextTimestamp,
            ]
        );

        $cache = new InMemoryCache(2, 60, $clockMock);
        $cache->set($key, $value, 1);
        $cache->set($key1, $value1);
        $cache->set($key2, $value2);

        $this->assertFalse(
            $cache->has($key),
            'expired item must be removed'
        );
        $this->assertTrue(
            $cache->has($key1),
            'valid item must be saved'
        );
        $this->assertTrue(
            $cache->has($key2),
            'new item must be added'
        );
    }

    public function testStackSizeUseFirstOneToReplace(): void
    {
        $key = 'test';
        $value = 'test value';
        $key1 = 'test_1';
        $value1 = 'test value 1';
        $key2 = 'test_2';
        $value2 = 'test value 2';

        $cache = new InMemoryCache(2, 60);
        $cache->set($key, $value);
        $cache->get($key);
        $cache->set($key1, $value1);
        $cache->get($key1);
        $cache->set($key2, $value2);

        $this->assertFalse(
            $cache->has($key),
            'in case of equal counters, first item must be removed'
        );
        $this->assertTrue(
            $cache->has($key1),
            'in case of equal counters, next items must be saved'
        );
        $this->assertTrue(
            $cache->has($key2),
            'new item must be added'
        );
    }

    /**
     * @param int[] $freezeAt
     */
    private function createClockMock(array $freezeAt = []): ClockInterface
    {
        return new class($freezeAt) implements ClockInterface {
            private int $counter = 0;

            public function __construct(
                /** @var int[] */
                private readonly array $freezeAt,
            ) {
            }

            public function now(): \DateTimeImmutable
            {
                $frozen = $this->freezeAt[$this->counter] ?? null;
                ++$this->counter;

                return (new \DateTimeImmutable())->setTimestamp($frozen ?? time());
            }
        };
    }
}
