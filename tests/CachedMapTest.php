<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache\Tests;

use Marvin255\InMemoryCache\CachedItem;
use Marvin255\InMemoryCache\CachedMap;

/**
 * @internal
 */
final class CachedMapTest extends BaseCase
{
    public function testIterator(): void
    {
        $key1 = 'key_1';
        $item1 = new CachedItem(1, 1);

        $key2 = 'key_2';
        $item2 = new CachedItem(2, 2);

        $map = new CachedMap();
        $map->set($key1, $item1);
        $map->set($key2, $item2);

        $res = [];
        foreach ($map as $key => $value) {
            $res[$key] = $value;
        }

        $res = [];
        foreach ($map as $key => $value) {
            $res[$key] = $value;
        }

        $this->assertSame(
            [
                $key1 => $item1,
                $key2 => $item2,
            ],
            $res
        );
    }

    public function testSetGet(): void
    {
        $key1 = 'key_1';
        $item1 = new CachedItem(1, 1);

        $key2 = 'key_2';
        $item2 = new CachedItem(2, 2);

        $key3 = 'key_3';

        $map = new CachedMap();
        $map->set($key1, $item1);
        $map->set($key2, $item2);

        $this->assertSame($item1, $map->get($key1));
        $this->assertSame($item2, $map->get($key2));
        $this->assertNull($map->get($key3));
    }

    public function testDelete(): void
    {
        $key1 = 'key_1';
        $item1 = new CachedItem(1, 1);

        $map = new CachedMap();
        $map->set($key1, $item1);
        $map->delete($key1);

        $this->assertNull($map->get($key1));
    }

    public function testClear(): void
    {
        $key1 = 'key_1';
        $item1 = new CachedItem(1, 1);

        $key2 = 'key_2';
        $item2 = new CachedItem(2, 2);

        $map = new CachedMap();
        $map->set($key1, $item1);
        $map->set($key2, $item2);
        $map->clear();

        $this->assertNull($map->get($key1));
        $this->assertNull($map->get($key2));
    }
}
