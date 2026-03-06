<?php

namespace DataBuilder\Cache;

/**
 * Interface PSR-16 simplifiée (Compatible SimpleCache)
 */
interface CacheInterface
{
    public function get(string $key, $default = null);
    public function set(string $key, mixed $value, int $ttl = 0): bool;
    public function delete(string $key): bool;
    public function has(string $key): bool;
    public function clear(): bool;
}