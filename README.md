# InMemoryCache

[![Build Status](https://github.com/marvin255/in-memory-cache/workflows/marvin255_in_memory_cache/badge.svg)](https://github.com/marvin255/in-memory-cache/actions?query=workflow%3A%22marvin255_in_memory_cache%22)

Simple [PSR-16](https://www.php-fig.org/psr/psr-16/) implementation which uses internal array to store data.



## Usage

```php
use Marvin255\InMemoryCache\InMemoryCache;

$maxCacheSize = 10000; // only 10000 can be stored by this object
$defaultTTL = 60;      // 60 seconds as default TTL
$cache = new InMemoryCache($maxCacheSize, $defaultTTL);
```



## Decorator

Decorator allows to use two caches in the same time. All data from basic cache (e.g. redis based cache) will be also stored in InMemoryCache. This decorator can reduce requests amount for long-living php processes.

```php
use Marvin255\InMemoryCache\InMemoryCache;
use Marvin255\InMemoryCache\CompositeCache;

$maxCacheSize = 10000; // only 10000 can be stored by this object
$defaultTTL = 60;      // 60 seconds as default TTL
$inMemoryCache = new InMemoryCache($maxCacheSize, $defaultTTL);

$redisCache = new MyAwesomeRedisCache();

$decorator = CompositeCache($inMemoryCache, $redisCache);

$decorator->get('test'); // this get will trigger a request to redis and save data to memory
$decorator->get('test'); // this get won't trigger any requests and just return data from memory
```
