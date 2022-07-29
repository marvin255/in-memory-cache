<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache;

use DateInterval;
use Psr\SimpleCache\CacheInterface;

/**
 * Simple PSR-16 implementation that uses internal array.
 */
class InMemoryCache implements CacheInterface
{
    private readonly int $stackSize;

    private readonly int $defaultTTL;

    /**
     * @var CachedItem[]
     */
    private array $stack = [];

    public function __construct(int $stackSize = 1000, int $defaultTTL = 60)
    {
        if ($stackSize < 1) {
            throw new InvalidArgumentException('Stack size must be greater than 0');
        }
        if ($defaultTTL < 1) {
            throw new InvalidArgumentException('Default TTL must be greater than 0');
        }

        $this->stackSize = $stackSize;
        $this->defaultTTL = $defaultTTL;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $item = $this->stack[$key] ?? null;

        return $item !== null && $this->isItemValid($item)
            ? $item->getPayload()
            : $default;
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        if (\count($this->stack) >= $this->stackSize) {
            $this->clearStack();
        }

        $validTill = $this->createValidTill($ttl);
        $this->stack[$key] = new CachedItem($value, $validTill);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key): bool
    {
        unset($this->stack[$key]);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function clear(): bool
    {
        $this->stack = [];

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set((string) $key, $value, $ttl);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $key): bool
    {
        return isset($this->stack[$key]) && $this->isItemValid($this->stack[$key]);
    }

    /**
     * Counts time before which cached item is valid.
     *
     * @param int|DateInterval|null $ttl
     *
     * @return int
     */
    private function createValidTill(null|int|DateInterval $ttl): int
    {
        $validTill = time();

        if ($ttl === null) {
            $validTill += $this->defaultTTL;
        } elseif ($ttl instanceof DateInterval) {
            $validTill += $ttl->s;
        } else {
            $validTill += $ttl;
        }

        return $validTill;
    }

    /**
     * Removes one item from stack to insert new one.
     *
     * Tries to remove expired item if there is some.
     * In other case removes item with the least select count.
     */
    private function clearStack(): void
    {
        $leastSelectCount = null;
        $keyToRemove = null;

        foreach ($this->stack as $key => $item) {
            if (!$this->isItemValid($item)) {
                $keyToRemove = $key;
                break;
            } elseif ($leastSelectCount === null || $leastSelectCount > $item->getSelectCount()) {
                $keyToRemove = $key;
                $leastSelectCount = $item->getSelectCount();
            }
        }

        if ($keyToRemove) {
            unset($this->stack[$keyToRemove]);
        }
    }

    /**
     * Checks that item valid and can be returned.
     *
     * @param CachedItem $item
     *
     * @return bool
     */
    private function isItemValid(CachedItem $item): bool
    {
        return $item->getValidTill() >= time();
    }
}
