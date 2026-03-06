<?php

namespace DataBuilder\Theme;

/**
 * Résout la chaîne de fallback d'un thème.
 * Résultat : ['custom', 'parent', 'base']  (du plus spécifique au plus général)
 *
 * Utilisé par ThemeManager, TemplateResolver et LayoutManager.
 */
class ThemeFallback
{
    /** @var ThemeConfig[] cache des configs chargées */
    private array $configs = [];
    private string $themesPath;

    public function __construct(string $themesPath)
    {
        $this->themesPath = $themesPath;
    }

    /**
     * Construit la chaîne complète pour un thème donné.
     *
     * @return string[]  ex: ['custom', 'parent', 'base']
     */
    public function buildChain(string $activeTheme): array
    {
        $chain   = [];
        $current = $activeTheme;
        $visited = [];

        while ($current !== '' && !in_array($current, $visited, true)) {
            $chain[]   = $current;
            $visited[] = $current;
            $current   = $this->getParent($current);
        }

        // Garantit que 'base' est toujours présent en dernier recours
        if (!in_array('base', $chain, true)) {
            $chain[] = 'base';
        }

        return $chain;
    }

    /**
     * Retourne le parent d'un thème, ou '' si aucun.
     */
    public function getParent(string $theme): string
    {
        $config = $this->getConfig($theme);
        if ($config === null) return '';
        return $config->getParent();
    }

    /**
     * Retourne la ThemeConfig d'un thème (avec cache interne).
     */
    public function getConfig(string $theme): ?ThemeConfig
    {
        if (isset($this->configs[$theme])) {
            return $this->configs[$theme];
        }

        $path = $this->themesPath . '/' . $theme;

        if (!is_dir($path)) return null;

        try {
            $config = new ThemeConfig($path);
            $config->load();
            $this->configs[$theme] = $config;
            return $config;
        } catch (\RuntimeException) {
            return null;
        }
    }

    /**
     * Liste tous les thèmes disponibles dans le dossier themes/
     */
    public function listAvailable(): array
    {
        $themes = [];
        foreach (glob($this->themesPath . '/*/theme.xml') as $file) {
            $themes[] = basename(dirname($file));
        }
        return $themes;
    }
}