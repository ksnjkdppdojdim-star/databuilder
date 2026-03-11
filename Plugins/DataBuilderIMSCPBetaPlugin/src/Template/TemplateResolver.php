<?php

namespace DataBuilder\Template;

/**
 * Résout le chemin absolu d'un template selon la hiérarchie des thèmes.
 * Ordre de recherche : custom → parent → base → modules
 */
class TemplateResolver
{
    private $themeChain = [];
    private $themesPath;
    private $modulesPath;

    public function __construct(array $config)
    {
        $this->themesPath  = $config['themes_path'];
        $this->modulesPath = $config['modules_path'];
        $this->themeChain  = $this->buildThemeChain($config['theme']);
    }

    /**
     * Résout le chemin absolu d'un template.
     * Retourne le premier fichier trouvé dans la hiérarchie.
     *
     * @param string $template  ex: "blocks/header.phtml"
     * @param string|null $module  ex: "example-module" (optionnel)
     */
    public function resolve(string $template, string $module = null): string
    {
        // 1. Cherche dans la chaîne de thèmes (du plus spécifique au plus général)
        foreach ($this->themeChain as $theme) {
            // Standard path: themes/{theme}/templates/{template}
            $path = $this->themesPath . "/{$theme}/templates/{$template}";
            if (file_exists($path)) {
                return $path;
            }
            // Direct path fallback: themes/{theme}/{template} (no templates/ subdir)
            $path = $this->themesPath . "/{$theme}/{$template}";
            if (file_exists($path)) {
                return $path;
            }
        }

        // 2. Cherche dans les templates du module si précisé
        if ($module !== null) {
            $path = $this->modulesPath . "/{$module}/templates/{$template}";
            if (file_exists($path)) {
                return $path;
            }
        }

        // 3. Cherche dans tous les modules (fallback global modules)
        foreach (glob($this->modulesPath . '/*/templates/' . $template) as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new \RuntimeException(
            "Template not found: [{$template}] " .
            "in theme chain [" . implode(' → ', $this->themeChain) . "]"
        );
    }

    /**
     * Vérifie si un template existe quelque part dans la hiérarchie
     */
    public function exists(string $template, string $module = null): bool
    {
        try {
            $this->resolve($template, $module);
            return true;
        } catch (\RuntimeException $e) {
            return false;
        }
    }

    /**
     * Construit la chaîne de thèmes du plus spécifique au plus général.
     * ex: ['custom', 'parent', 'base']
     */
    private function buildThemeChain(string $activeTheme): array
    {
        $chain   = [];
        $current = $activeTheme;
        $visited = [];

        while ($current && !in_array($current, $visited)) {
            $chain[]   = $current;
            $visited[] = $current;
            $current   = $this->getThemeParent($current);
        }

        // Assure que base est toujours en dernier recours
        if (!in_array('base', $chain)) {
            $chain[] = 'base';
        }

        return $chain;
    }

    private function getThemeParent(string $theme): ?string
    {
        $themeXml = $this->themesPath . "/{$theme}/theme.xml";
        if (!file_exists($themeXml)) return null;

        $xml = simplexml_load_file($themeXml);
        if ($xml === false) return null;

        $parent = (string)($xml->parent ?? '');
        return $parent !== '' ? $parent : null;
    }

    public function getThemeChain(): array
    {
        return $this->themeChain;
    }
}