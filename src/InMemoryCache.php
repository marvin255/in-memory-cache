<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache;

use Psr\SimpleCache\CacheInterface;

/**
 * Simple PSR-16 implementation that uses internal array.
 *
 * @psalm-api
 */
final class InMemoryCache implements CacheInterface
{
    private Timer $timer;

    /**
     * @var array<string, CachedItem>
     */
    private array $stack = [];

    public function __construct(
        private readonly int $stackSize = 1000,
        private readonly int $defaultTTL = 60,
        Timer $timer = null
    ) {
        if ($this->stackSize < 1) {
            throw new InvalidArgumentException('Stack size must be greater than 0');
        }
        if ($this->defaultTTL < 1) {
            throw new InvalidArgumentException('Default TTL must be greater than 0');
        }
        $this->timer = $timer ?: TimerFactory::create();
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
    public function set(string $key, mixed $value, int|\DateInterval $ttl = null): bool
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
    public function setMultiple(iterable $values, int|\DateInterval $ttl = null): bool
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
     */
    private function createValidTill(null|int|\DateInterval $ttl): int
    {
        $validTill = $this->timer->getCurrentTimestamp();

        if ($ttl === null) {
            $validTill += $this->defaultTTL;
        } elseif ($ttl instanceof \DateInterval) {
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
        $leastScore = null;
        $keyToRemove = null;

        foreach ($this->stack as $key => $item) {
            if (!$this->isItemValid($item)) {
                $keyToRemove = $key;
                break;
            }
            $score = $this->calculateItemSortScore($item);
            if ($leastScore === null || $leastScore > $score) {
                $keyToRemove = $key;
                $leastScore = $score;
            }
        }

        if ($keyToRemove) {
            unset($this->stack[$keyToRemove]);
        }
    }

    /**
     * Checks that item valid and can be returned.
     */
    private function isItemValid(CachedItem $item): bool
    {
        return $item->getValidTill() >= $this->timer->getCurrentTimestamp();
    }

    /**
     * Calculates score for item. Item with the least score will be removed in a case when stack is full.
     */
    private function calculateItemSortScore(CachedItem $item): int
    {
        return $item->getSelectCount();
    }
}
