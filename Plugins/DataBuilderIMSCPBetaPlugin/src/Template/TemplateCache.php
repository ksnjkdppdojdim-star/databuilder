<?php

namespace DataBuilder\Template;

/**
 * Cache de compilation des templates .phtml
 *
 * Principe : copie le template dans le dossier cache avec un nom hashé.
 * Le fichier cache est invalidé si le template source est plus récent.
 *
 * Pour la v0.1, la "compilation" est une copie directe (pas de transformation).
 * En v0.2 on pourra ajouter une passe de minification ou de pré-processing.
 */
class TemplateCache
{
    private string $cachePath;

    public function __construct(string $cachePath)
    {
        $this->cachePath = $cachePath;

        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Retourne le chemin du fichier cache pour un template source.
     */
    public function getCachedPath(string $sourcePath): string
    {
        $hash = md5($sourcePath);
        return $this->cachePath . '/' . $hash . '.phtml';
    }

    /**
     * Vérifie si le cache est valide (existe et plus récent que la source).
     */
    public function isValid(string $sourcePath): bool
    {
        $cachedPath = $this->getCachedPath($sourcePath);

        if (!file_exists($cachedPath)) {
            return false;
        }

        // Invalide si la source a été modifiée après la mise en cache
        return filemtime($cachedPath) >= filemtime($sourcePath);
    }

    /**
     * Compile (copie) le template source vers le cache.
     */
    public function compile(string $sourcePath): void
    {
        if (!file_exists($sourcePath)) {
            throw new \RuntimeException("Cannot compile — source not found: [{$sourcePath}]");
        }

        $cachedPath = $this->getCachedPath($sourcePath);
        $content    = file_get_contents($sourcePath);

        // Passe de nettoyage basique (supprime les commentaires PHP inutiles en prod)
        // Extensible en v0.2 pour minification HTML, etc.
        if (file_put_contents($cachedPath, $content, LOCK_EX) === false) {
            throw new \RuntimeException("Failed to write template cache: [{$cachedPath}]");
        }
    }

    /**
     * Invalide le cache d'un template spécifique.
     */
    public function invalidate(string $sourcePath): void
    {
        $cachedPath = $this->getCachedPath($sourcePath);
        if (file_exists($cachedPath)) {
            unlink($cachedPath);
        }
    }

    /**
     * Vide tout le cache templates.
     */
    public function flush(): void
    {
        foreach (glob($this->cachePath . '/*.phtml') as $file) {
            unlink($file);
        }
    }
}