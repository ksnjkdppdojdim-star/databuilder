<?php

namespace DataBuilder\Layout;

use DataBuilder\Cache\CacheManager;

/**
 * Cache des layouts fusionnés.
 * Évite de re-parser et re-fusionner les XML à chaque requête.
 */
class LayoutCache
{
    private string $cacheDir;

    public function __construct(
        private CacheManager $cacheManager,
        string $cachePath
    ) {
        $this->cacheDir = $cachePath . '/layouts';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Retourne le layout fusionné depuis le cache, ou null si absent/invalide.
     */
    public function get(string $handle, array $sourceFiles): ?array
    {
        $key      = $this->buildKey($handle, $sourceFiles);
        $cached   = $this->cacheManager->get($key);

        if ($cached === null) return null;

        // Invalide si l'un des fichiers sources a été modifié
        if ($this->isStale($cached['mtime'], $sourceFiles)) {
            $this->cacheManager->delete($key);
            return null;
        }

        return $cached['layout'];
    }

    /**
     * Stocke un layout fusionné en cache.
     */
    public function set(string $handle, array $sourceFiles, array $layout): void
    {
        $key = $this->buildKey($handle, $sourceFiles);

        $this->cacheManager->set($key, [
            'layout' => $layout,
            'mtime'  => time(),
            'files'  => $sourceFiles,
        ]);
    }

    /**
     * Invalide le cache d'un handle spécifique.
     */
    public function invalidate(string $handle): void
    {
        // On supprime tous les fichiers .cache du dossier layouts qui contiennent ce handle
        foreach (glob($this->cacheDir . '/*.cache') as $file) {
            $data = unserialize(file_get_contents($file));
            if (isset($data['value']['files'])) {
                unlink($file);
            }
        }
    }

    /**
     * Vide tout le cache des layouts.
     */
    public function flush(): void
    {
        $this->cacheManager->clear();
    }

    // ─── Privé ────────────────────────────────────────────────────────────────

    private function buildKey(string $handle, array $sourceFiles): string
    {
        sort($sourceFiles);
        return 'layout_' . $handle . '_' . md5(implode('|', $sourceFiles));
    }

    /**
     * Vérifie si un fichier source a été modifié après la mise en cache.
     */
    private function isStale(int $cachedAt, array $sourceFiles): bool
    {
        foreach ($sourceFiles as $file) {
            if (file_exists($file) && filemtime($file) > $cachedAt) {
                return true;
            }
        }
        return false;
    }
}