<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache\Tests;

use DateInterval;
use Marvin255\InMemoryCache\InMemoryCache;
use Marvin255\InMemoryCache\InvalidArgumentException;
use stdClass;

/**
 * @internal
 */
class InMemoryCacheTest extends BaseCase
{
    public function testGet(): void
    {
        $key = 'test';
        $value = 'test value';
        $ttl = 60;

        $cache = new InMemoryCache();
        $cache->set($key, $value, $ttl);

        $this->assertSame($value, $cache->get($key));
    }

    public function testGetObject(): void
    {
        $key = 'test';
        $value = new stdClass();
        $value->test = 'test value';
        $ttl = 60;

        $cache = new InMemoryCache();
        $cache->set($key, $value, $ttl);

        $this->assertSame($value, $cache->get($key));
    }

    public function testGetDefault(): void
    {
        $key = 'test';
        $default = 'default';

        $cache = new InMemoryCache();

        $this->assertSame($default, $cache->get($key, $default));
    }

    public function testGetAfterTtl(): void
    {
        $key = 'test';
        $value = 'test value';
        $ttl = 1;

        $cache = new InMemoryCache();
        $cache->set($key, $value, $ttl);
        sleep(2);

        $this->assertNull($cache->get($key, null));
    }

    /**
     * @psalm-suppress InvalidScalarArgument
     */
    public function testGetKeyTypeException(): void
    {
        $cache = new InMemoryCache();

        $this->expectException(InvalidArgumentException::class);
        $cache->get(true);
    }

    public function testSetDateInterval(): void
    {
        $key = 'test';
        $value = 'test value';
        $ttl = new DateInterval('PT1S');

        $cache = new InMemoryCache();
        $cache->set($key, $value, $ttl);
        sleep(2);

        $this->assertNull($cache->get($key, null));
    }

    /**
     * @psalm-suppress InvalidScalarArgument
     */
    public function testSetKeyTypeException(): void
    {
        $cache = new InMemoryCache();

        $this->expectException(InvalidArgumentException::class);
        $cache->set(true, 'test');
    }

    public function testSetMultiple(): void
    {
        $key = 'test';
        $value = 'test value';
        $key1 = 'test_1';
        $value1 = 'test value 1';
        $ttl = 60;

        $cache = new InMemoryCache();
        $cache->setMultiple([$key => $value, $key1 => $value1], $ttl);

        $this->assertSame($value, $cache->get($key));
        $this->assertSame($value1, $cache->get($key1));
    }

    /**
     * @psalm-suppress InvalidArgument
     */
    public function testSetMultipleKeysTypeException(): void
    {
        $cache = new InMemoryCache();

        $this->expectException(InvalidArgumentException::class);
        $cache->setMultiple(true);
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

        $this->assertSame(
            [
                $key2 => $default,
                $key1 => $value1,
                $key => $value,
            ],
            $cache->getMultiple([$key2, $key1, $key], $default)
        );
    }

    /**
     * @psalm-suppress InvalidArgument
     */
    public function testGetMultipleKeysTypeException(): void
    {
        $cache = new InMemoryCache();

        $this->expectException(InvalidArgumentException::class);
        $cache->getMultiple(true);
    }

    public function testHas(): void
    {
        $key = 'test';
        $value = 'test value';
        $ttl = 60;

        $cache = new InMemoryCache();
        $cache->set($key, $value, $ttl);

        $this->assertTrue($cache->has($key));
    }

    public function testDoesNotHave(): void
    {
        $key = 'test';
        $value = 'test value';
        $ttl = 60;

        $cache = new InMemoryCache();
        $cache->set($key, $value, $ttl);

        $this->assertFalse($cache->has('unexisted'));
    }

    public function testHasExpired(): void
    {
        $key = 'test';
        $value = 'test value';
        $ttl = 1;

        $cache = new InMemoryCache();
        $cache->set($key, $value, $ttl);
        sleep(2);

        $this->assertFalse($cache->has($key));
    }

    /**
     * @psalm-suppress InvalidScalarArgument
     */
    public function testHasKeyTypeException(): void
    {
        $cache = new InMemoryCache();

        $this->expectException(InvalidArgumentException::class);
        $cache->has(true);
    }

    public function testDelete(): void
    {
        $key = 'test';
        $value = 'test value';
        $ttl = 60;

        $cache = new InMemoryCache();
        $cache->set($key, $value, $ttl);
        $cache->delete($key);

        $this->assertFalse($cache->has($key));
    }

    /**
     * @psalm-suppress InvalidScalarArgument
     */
    public function testDeleteKeyTypeException(): void
    {
        $cache = new InMemoryCache();

        $this->expectException(InvalidArgumentException::class);
        $cache->delete(true);
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
        $cache->deleteMultiple([$key2, $key]);

        $this->assertFalse($cache->has($key));
        $this->assertSame($value1, $cache->get($key1));
        $this->assertFalse($cache->has($key2));
    }

    /**
     * @psalm-suppress InvalidArgument
     */
    public function testDeleteMultipleKeysTypeException(): void
    {
        $cache = new InMemoryCache();

        $this->expectException(InvalidArgumentException::class);
        $cache->deleteMultiple(true);
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
        $cache->clear();

        $this->assertFalse($cache->has($key));
        $this->assertFalse($cache->has($key1));
    }

    public function testStackSize(): void
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

        $this->assertFalse($cache->has($key), 'Item that cleared from cache');
        $this->assertTrue($cache->has($key1), 'Item that was selected once');
        $this->assertTrue($cache->has($key2), 'New item');
    }

    public function testStackSizeTTL(): void
    {
        $key = 'test';
        $value = 'test value';
        $key1 = 'test_1';
        $value1 = 'test value 1';
        $key2 = 'test_2';
        $value2 = 'test value 2';

        $cache = new InMemoryCache(2, 60);
        $cache->set($key, $value, 1);
        $cache->set($key1, $value1);
        sleep(2);
        $cache->set($key2, $value2);

        $this->assertFalse($cache->has($key), 'Item that was expired');
        $this->assertTrue($cache->has($key1), 'Common item');
        $this->assertTrue($cache->has($key2), 'New item');
    }
}
