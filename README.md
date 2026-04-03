# InMemoryCache

[![Build Status](https://github.com/marvin255/in-memory-cache/workflows/marvin255_in_memory_cache/badge.svg)](https://github.com/marvin255/in-memory-cache/actions?query=workflow%3A%22marvin255_in_memory_cache%22)

Simple [PSR-16](https://www.php-fig.org/psr/psr-16/) implementation which uses internal array to store data.



## Usage

```php
use Marvin255\InMemoryCache\InMemoryCache;

$maxCacheSize = 10000;  // only 10000 can be stored by this object
$defaultTTL = 60;       // 60 seconds as default TTL

$cache = new InMemoryCache($maxCacheSize, $defaultTTL);
```



## Decorator

The decorator allows you to use two caches at the same time. All data from the base cache (e.g. a Redis-based cache) will also be stored in InMemoryCache. This decorator can reduce the number of requests for long-running PHP processes.

```php
use Marvin255\InMemoryCache\InMemoryCache;
use Marvin255\InMemoryCache\CompositeCache;

$maxCacheSize = 10000;  // only 10000 can be stored by this object
$defaultTTL = 60;       // 60 seconds as default TTL

$inMemoryCache = new InMemoryCache($maxCacheSize, $defaultTTL);
$redisCache = new MyAwesomeRedisCache();
$decorator = new CompositeCache($inMemoryCache, $redisCache);

$decorator->get('test'); // this call will trigger a request to Redis and save the data to memory
$decorator->get('test'); // this call won't trigger any requests and will just return data from memory
```
