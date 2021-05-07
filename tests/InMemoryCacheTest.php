<?php

declare(strict_types=1);

namespace Marvin255\Jwt\InMemoryCache\Tests;

use Marvin255\Jwt\InMemoryCache\InMemoryCache;
use Psr\SimpleCache\InvalidArgumentException;
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
        $cache->set($key, $value, $ttl);

        $this->assertSame($default, $cache->get($key, $default));
    }

    public function testGetAfterTtl(): void
    {
        $key = 'test';
        $value = 'test value';
        $ttl = 1;

        $cache = new InMemoryCache();
        $cache->set($key, $value, $ttl);
        sleep(1);

        $this->assertNull($cache->get($key, null));
    }

    public function testGetIllegalKey(): void
    {
        $cache = new InMemoryCache();

        $this->expectException(InvalidArgumentException::class);
        $cache->get(true);
    }

    public function testSetIllegalKey(): void
    {
        $cache = new InMemoryCache();

        $this->expectException(InvalidArgumentException::class);
        $cache->set(true, true);
    }

    public function testSetMultiple(): void
    {
        $key = 'test';
        $value = 'test value';
        $key1 = 'test_1';
        $value1 = 'test value 1';
        $ttl = 60;

        $cache = new InMemoryCache();
        $cache->setMultiple([$key => $value, $key1 => $value], $ttl);

        $this->assertSame($value, $cache->get($key));
        $this->assertSame($value1, $cache->get($key1));
    }

    public function testSetMultipleIllegalKey(): void
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
            $cache->getMultiple([$key2, $key1, $key])
        );
    }

    public function testGetMultipleIllegalKey(): void
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

    public function testHasIllegalKey(): void
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

    public function testDeleteIllegalKey(): void
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

    public function testDeleteMultipleIllegalKey(): void
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
}
