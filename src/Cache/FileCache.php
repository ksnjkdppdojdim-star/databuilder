<?php

namespace DataBuilder\Cache;

/**
 * Backend de cache fichier.
 * Stocke les données sérialisées dans cache/ avec TTL optionnel.
 */
class FileCache implements CacheInterface
{
    public function __construct(private string $cachePath)
    {
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $file = $this->getFilePath($key);

        if (!file_exists($file)) return $default;

        $data = unserialize(file_get_contents($file));

        if (!is_array($data) || !isset($data['value'], $data['expires'])) {
            return $default;
        }

        // TTL expiré
        if ($data['expires'] !== 0 && $data['expires'] < time()) {
            $this->delete($key);
            return $default;
        }

        return $data['value'];
    }

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        $data = [
            'value'   => $value,
            'expires' => $ttl > 0 ? time() + $ttl : 0,
        ];

        return file_put_contents(
            $this->getFilePath($key),
            serialize($data),
            LOCK_EX
        ) !== false;
    }

    public function delete(string $key): bool
    {
        $file = $this->getFilePath($key);
        return file_exists($file) ? unlink($file) : true;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function clear(): bool
    {
        foreach (glob($this->cachePath . '/*.cache') as $file) {
            unlink($file);
        }
        return true;
    }

    private function getFilePath(string $key): string
    {
        return $this->cachePath . '/' . md5($key) . '.cache';
    }
}