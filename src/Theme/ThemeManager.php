<?php

namespace DataBuilder\Theme;

/**
 * Point d'entrée central pour tout ce qui concerne les thèmes.
 * Expose la chaîne de fallback, les configs, et le thème actif.
 * Remplace les résolutions de thème inline dispersées dans TemplateResolver et LayoutManager.
 */
class ThemeManager
{
    private ThemeFallback $fallback;
    private string $activeTheme;
    private ?array $chain = null; // Cache de la chaîne résolue

    public function __construct(array $config)
    {
        $this->fallback    = new ThemeFallback($config['themes_path']);
        $this->activeTheme = $config['theme'] ?? 'base';
    }

    /**
     * Retourne la chaîne de fallback du thème actif.
     * Résultat mis en cache après le premier appel.
     *
     * @return string[]  ex: ['custom', 'parent', 'base']
     */
    public function getChain(): array
    {
        if ($this->chain === null) {
            $this->chain = $this->fallback->buildChain($this->activeTheme);
        }
        return $this->chain;
    }

    /**
     * Cherche un fichier dans la hiérarchie de thèmes.
     * Retourne le chemin absolu du premier fichier trouvé, ou null.
     *
     * @param string $relativePath  ex: "layouts/homepage.xml" ou "templates/blocks/header.phtml"
     */
    public function resolveFile(string $relativePath, string $themesPath): ?string
    {
        foreach ($this->getChain() as $theme) {
            $path = $themesPath . '/' . $theme . '/' . $relativePath;
            if (file_exists($path)) {
                return $path;
            }
        }
        return null;
    }

    public function getActiveTheme(): string
    {
        return $this->activeTheme;
    }

    public function setActiveTheme(string $theme): void
    {
        $this->activeTheme = $theme;
        $this->chain       = null; // Invalide le cache de chaîne
    }

    public function getThemeConfig(string $theme): ?ThemeConfig
    {
        return $this->fallback->getConfig($theme);
    }

    public function listAvailable(): array
    {
        return $this->fallback->listAvailable();
    }

    /**
     * Invalide le cache de la chaîne (utile si le thème actif change à runtime)
     */
    public function flush(): void
    {
        $this->chain = null;
    }
}