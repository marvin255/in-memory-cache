<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache;

use Psr\SimpleCache\CacheInterface;

/**
 * PSR-16 cache that uses two diffrent caches to store data.
 *
 * The first cache object is a light and fast cache e.g. InMemoryCache.
 * The second cache object is something that requires socket connection e.g. redis based cache.
 * Composite cache tries to find data in the light cache, and only if there is no data,
 * queries the heavy cache.
 *
 * @psalm-api
 */
final class CompositeCache implements CacheInterface
{
    public function __construct(
        private readonly CacheInterface $lightCache,
        private readonly CacheInterface $heavyCache,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
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
    #[\Override]
    public function set(string $key, mixed $value, int|\DateInterval|null $ttl = null): bool
    {
        return $this->heavyCache->set($key, $value, $ttl)
            && $this->lightCache->set($key, $value, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function delete(string $key): bool
    {
        return $this->heavyCache->delete($key)
            && $this->lightCache->delete($key);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function clear(): bool
    {
        return $this->heavyCache->clear()
            && $this->lightCache->clear();
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
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
    #[\Override]
    public function setMultiple(iterable $values, int|\DateInterval|null $ttl = null): bool
    {
        return $this->heavyCache->setMultiple($values, $ttl)
            && $this->lightCache->setMultiple($values, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function deleteMultiple(iterable $keys): bool
    {
        return $this->heavyCache->deleteMultiple($keys)
            && $this->lightCache->deleteMultiple($keys);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function has(string $key): bool
    {
        return $this->lightCache->has($key)
            || $this->heavyCache->has($key);
    }
}
