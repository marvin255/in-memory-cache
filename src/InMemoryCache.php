<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache;

use Psr\Clock\ClockInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Simple PSR-16 implementation that uses internal array.
 *
 * @psalm-api
 */
final class InMemoryCache implements CacheInterface
{
    public const DEFAULT_STACK_SIZE = 1000;
    public const DEFAULT_TTL = 60;

    private readonly CachedMap $cachedMap;

    private readonly ClockInterface $clock;

    public function __construct(
        private readonly int $stackSize = self::DEFAULT_STACK_SIZE,
        private readonly int $defaultTTL = self::DEFAULT_TTL,
        ?ClockInterface $clock = null,
    ) {
        if ($this->stackSize < 1) {
            throw new InvalidArgumentException('Stack size must be greater than 0');
        }
        if ($this->defaultTTL < 1) {
            throw new InvalidArgumentException('Default TTL must be greater than 0');
        }
        $this->cachedMap = new CachedMap();
        $this->clock = $clock ?: new Clock();
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function get(string $key, mixed $default = null): mixed
    {
        $item = $this->cachedMap->get($key);

        return $this->isItemValid($item) ? $item->getPayload() : $default;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function set(string $key, mixed $value, int|\DateInterval|null $ttl = null): bool
    {
        if (\count($this->cachedMap) >= $this->stackSize) {
            $this->clearMap();
        }

        $validTill = $this->createValidTill($ttl);
        $this->cachedMap->set($key, new CachedItem($value, $validTill));

        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function delete(string $key): bool
    {
        $this->cachedMap->delete($key);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function clear(): bool
    {
        $this->cachedMap->clear();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
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
    #[\Override]
    public function setMultiple(iterable $values, int|\DateInterval|null $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set((string) $key, $value, $ttl);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
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
    #[\Override]
    public function has(string $key): bool
    {
        return $this->isItemValid(
            $this->cachedMap->get($key)
        );
    }

    /**
     * Counts time till cached item is valid.
     */
    private function createValidTill(int|\DateInterval|null $ttl): int
    {
        $validTill = $this->clock->now()->getTimestamp();

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
     * Removes one item from the map to insert a new one.
     *
     * It tries to remove an expired item if there is any.
     * In other case it removes an item with the lowest select count value.
     */
    private function clearMap(): void
    {
        $leastScore = null;
        $keyToRemove = null;

        foreach ($this->cachedMap as $key => $item) {
            if (!$this->isItemValid($item)) {
                $keyToRemove = $key;
                break;
            }
            $score = $item->getSelectCount();
            if ($leastScore === null || $leastScore > $score) {
                $keyToRemove = $key;
                $leastScore = $score;
            }
        }

        if ($keyToRemove !== null) {
            $this->cachedMap->delete($keyToRemove);
        }
    }

    /**
     * Checks that item is valid and can be returned.
     *
     * @psalm-assert-if-true CachedItem $item
     */
    private function isItemValid(?CachedItem $item): bool
    {
        return $item !== null
            && $item->getValidTill() >= $this->clock->now()->getTimestamp();
    }
}
