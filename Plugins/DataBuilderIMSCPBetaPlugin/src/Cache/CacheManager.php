<?php

namespace DataBuilder\Cache;

/**
 * Interface unifiée — facade devant le backend de cache actif.
 * Permet de swapper FileCache → Redis → Memcached sans toucher au reste du code.
 */
class CacheManager
{
    private CacheInterface $driver;

    public function __construct(array $config)
    {
        $cachePath    = $config['cache_path'] ?? 'cache';
        $this->driver = new FileCache($cachePath . '/data');
    }

    /**
     * Permet d'injecter un driver custom (ex: RedisCache implements CacheInterface)
     */
    public function setDriver(CacheInterface $driver): void
    {
        $this->driver = $driver;
    }

    public function get(string $key, $default = null)
    {
        return $this->driver->get($key, $default);
    }

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        return $this->driver->set($key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        return $this->driver->delete($key);
    }

    public function has(string $key): bool
    {
        return $this->driver->has($key);
    }

    public function clear(): bool
    {
        return $this->driver->clear();
    }

    /**
     * Cache avec callback — pattern "get or compute"
     * Usage : $cache->remember('my_key', fn() => expensiveCompute(), 3600)
     */
    public function remember(string $key, callable $callback, int $ttl = 0)
    {
        $value = $this->get($key);

        if ($value !== null) return $value;

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }
}