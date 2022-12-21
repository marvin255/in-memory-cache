<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache;

use Psr\SimpleCache\CacheInterface;

/**
 * PSR-16 cache that uses two diffrent caches to store data.
 *
 * The first is a light and fast cache e.g. InMemoryCache.
 * The second is something that require socket connection e.g. redis based cache.
 * Composite cache tries to find data in the light cache and if there is no data
 * in light cache makes a request for heavy cache.
 */
final class CompositeCache implements CacheInterface
{
    private readonly CacheInterface $lightCache;

    private readonly CacheInterface $heavyCache;

    public function __construct(CacheInterface $lightCache, CacheInterface $heavyCache)
    {
        $this->lightCache = $lightCache;
        $this->heavyCache = $heavyCache;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->lightCache->has($key)) {
            return $this->lightCache->get($key, $default);
        }

        $value = $this->heavyCache->get($key, $default);
        $this->lightCache->set($key, $value);

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        return $this->heavyCache->set($key, $value, $ttl)
            && $this->lightCache->set($key, $value, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key): bool
    {
        return $this->heavyCache->delete($key)
            && $this->lightCache->delete($key);
    }

    /**
     * {@inheritDoc}
     */
    public function clear(): bool
    {
        return $this->heavyCache->clear()
            && $this->lightCache->clear();
    }

    /**
     * {@inheritDoc}
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $return = [];
        foreach ($keys as $key) {
            $return[$key] = $this->get($key, $default);
        }

        return $return;
    }

    /**
     * {@inheritDoc}
     */
    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        return $this->heavyCache->setMultiple($values, $ttl)
            && $this->lightCache->setMultiple($values, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteMultiple(iterable $keys): bool
    {
        return $this->heavyCache->deleteMultiple($keys)
            && $this->lightCache->deleteMultiple($keys);
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $key): bool
    {
        return $this->lightCache->has($key)
            || $this->heavyCache->has($key);
    }
}
