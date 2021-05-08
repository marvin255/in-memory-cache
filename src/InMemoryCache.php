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
    private int $stackSize;

    private int $defaultTTL;

    /**
     * @var CachedItem[]
     */
    private array $stack = [];

    public function __construct(int $stackSize = 1000, int $defaultTTL = 60)
    {
        $this->stackSize = $stackSize;
        $this->defaultTTL = $defaultTTL;
    }

    /**
     * {@inheritDoc}
     */
    public function get($key, $default = null)
    {
        $unifiedKey = $this->checkAndUnifyKey($key);

        if (!isset($this->stack[$unifiedKey])) {
            return $default;
        } elseif (!$this->stack[$unifiedKey]->isValid()) {
            unset($this->stack[$unifiedKey]);

            return $default;
        }

        ++$this->stack[$unifiedKey]->selectCount;

        return $this->stack[$unifiedKey]->payload;
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value, $ttl = null)
    {
        $unifiedKey = $this->checkAndUnifyKey($key);

        if (\count($this->stack) >= $this->stackSize) {
            $this->clearStack();
        }

        $this->stack[$unifiedKey] = new CachedItem(
            $value,
            $this->createValidTill($ttl)
        );

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function delete($key)
    {
        $unifiedKey = $this->checkAndUnifyKey($key);
        unset($this->stack[$unifiedKey]);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        $this->stack = [];

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getMultiple($keys, $default = null)
    {
        $unifiedKeys = $this->checkAndUnifyKeys($keys);

        $result = [];
        foreach ($unifiedKeys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function setMultiple($values, $ttl = null)
    {
        $unifiedValues = $this->checkAndUnifyKeys($values);

        foreach ($unifiedValues as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteMultiple($keys)
    {
        $unifiedKeys = $this->checkAndUnifyKeys($keys);

        foreach ($unifiedKeys as $key) {
            $this->delete($key);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        $unifiedKey = $this->checkAndUnifyKey($key);

        return isset($this->stack[$unifiedKey]) && $this->stack[$unifiedKey]->isValid();
    }

    /**
     * Removes one item from stack to insert new one.
     *
     * Tries to remove expired item if there is some.
     * In other case removes  item with the least select count.
     */
    private function clearStack(): void
    {
        $now = time();
        $leastSelectCount = null;
        $keyToRemove = null;

        foreach ($this->stack as $key => $item) {
            if ($item->validTill < $now) {
                $keyToRemove = $key;
                break;
            } elseif ($leastSelectCount === null || $leastSelectCount > $item->selectCount) {
                $keyToRemove = $key;
                $leastSelectCount = $item->selectCount;
            }
        }

        if ($keyToRemove) {
            unset($this->stack[$keyToRemove]);
        }
    }

    /**
     * Check that cache key is valid item and returns it as a string.
     *
     * @param mixed $key
     *
     * @return string
     */
    private function checkAndUnifyKey($key): string
    {
        if (!is_scalar($key)) {
            $message = 'Cache key must be a string instance.';
            throw new InvalidArgumentException($message);
        }

        return (string) $key;
    }

    /**
     * Check that cache keys item is a iterable object.
     *
     * @param mixed $keys
     *
     * @return iterable
     */
    private function checkAndUnifyKeys($keys): iterable
    {
        if (!is_iterable($keys)) {
            $message = 'Keys must be an iterable instance.';
            throw new InvalidArgumentException($message);
        }

        return $keys;
    }

    /**
     * Counts time untill cached item is valid.
     *
     * @param mixed $ttl
     *
     * @return int
     */
    private function createValidTill($ttl): int
    {
        $validTill = time();

        if ($ttl === null) {
            $validTill += $this->defaultTTL;
        } elseif ($ttl instanceof DateInterval) {
            $validTill += $ttl->s;
        } else {
            $validTill += (int) $ttl;
        }

        return $validTill;
    }
}
