<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache\Tests;

use Marvin255\InMemoryCache\CompositeCache;
use Psr\SimpleCache\CacheInterface;

/**
 * @internal
 */
class CompositeCacheTest extends BaseCase
{
    public function testGetLight(): void
    {
        $key = 'key';
        $value = 'value';
        $default = 'default';

        $light = $this->getMockBuilder(CacheInterface::class)->getMock();
        $light->method('has')
            ->with($this->equalTo($key))
            ->willReturn(true)
        ;
        $light->method('get')
            ->with($this->equalTo($key), $this->equalTo($default))
            ->willReturn($value)
        ;

        $heavy = $this->getMockBuilder(CacheInterface::class)->getMock();
        $heavy->expects($this->never())->method('get');

        $cache = new CompositeCache($light, $heavy);
        $gotValue = $cache->get($key, $default);

        $this->assertSame($value, $gotValue);
    }

    public function testGetHeavy(): void
    {
        $key = 'key';
        $value = 'value';
        $default = 'default';

        $light = $this->getMockBuilder(CacheInterface::class)->getMock();
        $light->expects($this->once())
            ->method('has')
            ->with($this->equalTo($key))
            ->willReturn(false)
        ;
        $light->expects($this->once())
            ->method('set')
            ->with($this->equalTo($key), $this->equalTo($value))
        ;

        $heavy = $this->getMockBuilder(CacheInterface::class)->getMock();
        $heavy->expects($this->once())
            ->method('get')
            ->with($this->equalTo($key), $this->equalTo($default))
            ->willReturn($value)
        ;

        $cache = new CompositeCache($light, $heavy);
        $gotValue = $cache->get($key, $default);

        $this->assertSame($value, $gotValue);
    }

    public function testSet(): void
    {
        $key = 'key';
        $value = 'value';
        $ttl = 60;

        $light = $this->getMockBuilder(CacheInterface::class)->getMock();
        $light->expects($this->once())
            ->method('set')
            ->with($this->equalTo($key), $this->equalTo($value), $this->equalTo($ttl))
            ->willReturn(true)
        ;

        $heavy = $this->getMockBuilder(CacheInterface::class)->getMock();
        $heavy->expects($this->once())
            ->method('set')
            ->with($this->equalTo($key), $this->equalTo($value), $this->equalTo($ttl))
            ->willReturn(true)
        ;

        $cache = new CompositeCache($light, $heavy);
        $cache->set($key, $value, $ttl);
    }

    public function testDelete(): void
    {
        $key = 'key';

        $light = $this->getMockBuilder(CacheInterface::class)->getMock();
        $light->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($key))
            ->willReturn(true)
        ;

        $heavy = $this->getMockBuilder(CacheInterface::class)->getMock();
        $heavy->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($key))
            ->willReturn(true)
        ;

        $cache = new CompositeCache($light, $heavy);
        $cache->delete($key);
    }

    public function testClear(): void
    {
        $light = $this->getMockBuilder(CacheInterface::class)->getMock();
        $light->expects($this->once())
            ->method('clear')
            ->willReturn(true)
        ;

        $heavy = $this->getMockBuilder(CacheInterface::class)->getMock();
        $heavy->expects($this->once())
            ->method('clear')
            ->willReturn(true)
        ;

        $cache = new CompositeCache($light, $heavy);
        $cache->clear();
    }

    public function testGetMultiple(): void
    {
        $key = 'key';
        $value = 'value';
        $key1 = 'key_1';
        $value1 = 'value 1';
        $default = 'default';

        $light = $this->getMockBuilder(CacheInterface::class)->getMock();
        $light->method('has')
            ->willReturnMap(
                [
                    [$key, false],
                    [$key1, true],
                ]
            )
        ;
        $light->method('get')
            ->with($this->equalTo($key1), $this->equalTo($default))
            ->willReturn($value1)
        ;
        $light->expects($this->once())
            ->method('set')
            ->with($this->equalTo($key), $this->equalTo($value))
        ;

        $heavy = $this->getMockBuilder(CacheInterface::class)->getMock();
        $heavy->method('get')
            ->with($this->equalTo($key), $this->equalTo($default))
            ->willReturn($value)
        ;

        $cache = new CompositeCache($light, $heavy);
        $multiple = $cache->getMultiple([$key, $key1], $default);

        $this->assertSame(
            [$key => $value, $key1 => $value1],
            $multiple
        );
    }

    public function testSetMultiple(): void
    {
        $values = ['key' => 'value'];
        $ttl = 60;

        $light = $this->getMockBuilder(CacheInterface::class)->getMock();
        $light->expects($this->once())
            ->method('setMultiple')
            ->with($this->equalTo($values), $this->equalTo($ttl))
            ->willReturn(true)
        ;

        $heavy = $this->getMockBuilder(CacheInterface::class)->getMock();
        $heavy->expects($this->once())
            ->method('setMultiple')
            ->with($this->equalTo($values), $this->equalTo($ttl))
            ->willReturn(true)
        ;

        $cache = new CompositeCache($light, $heavy);
        $cache->setMultiple($values, $ttl);
    }

    public function testDeleteMultiple(): void
    {
        $keys = ['key'];

        $light = $this->getMockBuilder(CacheInterface::class)->getMock();
        $light->expects($this->once())
            ->method('deleteMultiple')
            ->with($this->equalTo($keys))
            ->willReturn(true)
        ;

        $heavy = $this->getMockBuilder(CacheInterface::class)->getMock();
        $heavy->expects($this->once())
            ->method('deleteMultiple')
            ->with($this->equalTo($keys))
            ->willReturn(true)
        ;

        $cache = new CompositeCache($light, $heavy);
        $cache->deleteMultiple($keys);
    }

    public function testHasLight(): void
    {
        $key = 'key';

        $light = $this->getMockBuilder(CacheInterface::class)->getMock();
        $light->method('has')
            ->with($this->equalTo($key))
            ->willReturn(true)
        ;

        $heavy = $this->getMockBuilder(CacheInterface::class)->getMock();
        $heavy->expects($this->never())
            ->method('has')
            ->with($this->equalTo($key))
            ->willReturn(false)
        ;

        $cache = new CompositeCache($light, $heavy);
        $has = $cache->has($key);

        $this->assertTrue($has);
    }

    public function testHasHeavy(): void
    {
        $key = 'key';

        $light = $this->getMockBuilder(CacheInterface::class)->getMock();
        $light->method('has')
            ->with($this->equalTo($key))
            ->willReturn(false)
        ;

        $heavy = $this->getMockBuilder(CacheInterface::class)->getMock();
        $heavy->method('has')
            ->with($this->equalTo($key))
            ->willReturn(true)
        ;

        $cache = new CompositeCache($light, $heavy);
        $has = $cache->has($key);

        $this->assertTrue($has);
    }

    public function testDoesNotHave(): void
    {
        $key = 'key';

        $light = $this->getMockBuilder(CacheInterface::class)->getMock();
        $light->method('has')
            ->with($this->equalTo($key))
            ->willReturn(false)
        ;

        $heavy = $this->getMockBuilder(CacheInterface::class)->getMock();
        $heavy->method('has')
            ->with($this->equalTo($key))
            ->willReturn(false)
        ;

        $cache = new CompositeCache($light, $heavy);
        $has = $cache->has($key);

        $this->assertFalse($has);
    }
}
