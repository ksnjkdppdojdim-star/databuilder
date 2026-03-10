<?php

namespace DataBuilder\Layout;

use DataBuilder\Core\Registry;

/**
 * Orchestre le chargement, la fusion et la résolution des layouts.
 *
 * Ordre de chargement (du plus général au plus spécifique) :
 *   1. Layout global du thème base     (themes/base/layouts/default.xml)
 *   2. Layout global du thème parent   (themes/parent/layouts/default.xml) si existe
 *   3. Layout global du thème custom   (themes/custom/layouts/default.xml) si existe
 *   4. Layouts injectés par les modules (modules/*//*.xml)
 *   5. Layout de page spécifique       (ex: homepage.xml, catalog_index.xml)
 */
class LayoutManager
{
    private $config;
    private $registry;
    private $parser;
    private $merger;
    private $loadedLayouts = [];

    public function __construct(array $config, Registry $registry)
    {
        $this->config   = $config;
        $this->registry = $registry;
        $this->parser   = new XmlParser();
        $this->merger   = new LayoutMerger();
    }

    /**
     * Charge et retourne le layout fusionné pour un handle donné.
     * Un "handle" = nom de page (ex: "default", "homepage", "user_login")
     */
    public function getLayout(string $handle): array
    {
        if (isset($this->loadedLayouts[$handle])) {
            return $this->loadedLayouts[$handle];
        }

        $files = $this->resolveLayoutFiles($handle);

        if (empty($files)) {
            throw new \RuntimeException("No layout files found for handle: {$handle}");
        }

        $parser = $this->parser;
        $parsed = array_map(function ($file) use ($parser) { return $parser->parseFile($file); }, $files);
        $merged = $this->merger->mergeAll($parsed);

        $this->loadedLayouts[$handle] = $merged;

        return $merged;
    }

    /**
     * Résout la liste ordonnée des fichiers de layout à fusionner pour un handle.
     */
    private function resolveLayoutFiles(string $handle): array
    {
        $files = [];

        // 1. Thème base (obligatoire)
        $baseLayout = $this->config['themes_path'] . '/base/layouts/default.xml';
        if (file_exists($baseLayout)) {
            $files[] = $baseLayout;
        }

        // 2. Thème(s) intermédiaires via fallback hiérarchique
        $themeChain = $this->resolveThemeChain($this->config['theme']);
        foreach ($themeChain as $theme) {
            if ($theme === 'base') continue; // déjà chargé
            $themeLayout = $this->config['themes_path'] . "/{$theme}/layouts/default.xml";
            if (file_exists($themeLayout)) {
                $files[] = $themeLayout;
            }
        }

        // 3. Layouts des modules activés
        $moduleLayouts = $this->resolveModuleLayouts($handle);
        $files = array_merge($files, $moduleLayouts);

        // 4. Layout de page spécifique (override final par handle)
        if ($handle !== 'default') {
            $activeTheme = $this->config['theme'];
            $pageLayout  = $this->config['themes_path'] . "/{$activeTheme}/layouts/{$handle}.xml";
            if (file_exists($pageLayout)) {
                $files[] = $pageLayout;
            }
        }

        return array_unique($files);
    }

    /**
     * Retourne la chaîne de thèmes du plus générique au plus spécifique.
     * Ex: ['base', 'parent', 'custom']
     */
    private function resolveThemeChain(string $theme): array
    {
        $chain  = [];
        $current = $theme;
        $visited = [];

        while ($current && !in_array($current, $visited)) {
            array_unshift($chain, $current);
            $visited[] = $current;
            $current   = $this->getThemeParent($current);
        }

        // Assure que "base" est toujours en premier
        if (!in_array('base', $chain)) {
            array_unshift($chain, 'base');
        }

        return $chain;
    }

    /**
     * Lit le parent déclaré dans theme.xml d'un thème.
     */
    private function getThemeParent(string $theme): ?string
    {
        $themeXml = $this->config['themes_path'] . "/{$theme}/theme.xml";

        if (!file_exists($themeXml)) return null;

        $xml = simplexml_load_file($themeXml);
        if ($xml === false) return null;

        $parent = (string)($xml->parent ?? '');
        return $parent !== '' ? $parent : null;
    }

    /**
     * Collecte les layouts injectés par les modules pour un handle donné.
     */
    private function resolveModuleLayouts(string $handle): array
    {
        $files       = [];
        $modulesPath = $this->config['modules_path'];

        if (!is_dir($modulesPath)) return [];

        foreach (glob($modulesPath . '/*/layouts/*.xml') as $file) {
            $filename = basename($file, '.xml');
            // Charge le layout du module si son handle correspond ou si c'est "default"
            if ($filename === $handle || $filename === 'default') {
                $files[] = $file;
            }
        }

        return $files;
    }

    /**
     * Invalide le cache interne d'un handle (utile pour les tests ou hot-reload)
     */
    public function flush(string $handle = null): void
    {
        if ($handle) {
            unset($this->loadedLayouts[$handle]);
        } else {
            $this->loadedLayouts = [];
        }
    }
}
