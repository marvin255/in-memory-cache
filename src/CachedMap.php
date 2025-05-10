<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache;

/**
 * Map functionality for cached items.
 *
 * @internal
 *
 * @implements \Iterator<string, CachedItem>
 */
final class CachedMap implements \Countable, \Iterator
{
    /**
     * @var array<string, CachedItem>
     */
    private array $map = [];

    /**
     * Add new item associated with provided key.
     */
    public function set(string $key, CachedItem $item): void
    {
        $this->map[$key] = $item;
    }

    /**
     * Return item associated with provided key.
     */
    public function get(string $key): ?CachedItem
    {
        return $this->map[$key] ?? null;
    }

    /**
     * Delete item associated with provided key.
     */
    public function delete(string $key): void
    {
        unset($this->map[$key]);
    }

    /**
     * Remove all items and associations.
     */
    public function clear(): void
    {
        $this->map = [];
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress InvalidFalsableReturnType
     * @psalm-suppress FalsableReturnStatement
     */
    #[\Override]
    public function current(): CachedItem
    {
        return current($this->map);
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress InvalidNullableReturnType
     * @psalm-suppress NullableReturnStatement
     */
    #[\Override]
    public function key(): string
    {
        return key($this->map);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function next(): void
    {
        next($this->map);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function rewind(): void
    {
        reset($this->map);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function valid(): bool
    {
        return current($this->map) !== false;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function count(): int
    {
        return \count($this->map);
    }
}
