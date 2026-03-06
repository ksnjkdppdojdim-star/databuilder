<?php

namespace DataBuilder\Template;

use DataBuilder\Block\BlockInterface;

/**
 * Moteur de rendu des templates .phtml
 * Chaque template reçoit $block comme variable principale.
 * Le rendu est isolé dans une closure pour éviter la pollution de scope.
 */
class TemplateEngine
{
    private TemplateResolver $resolver;
    private TemplateCache $cache;
    private bool $cacheEnabled;
    private bool $debug;

    public function __construct(array $config)
    {
        $this->resolver     = new TemplateResolver($config);
        $this->cache        = new TemplateCache($config['cache_path'] . '/templates');
        $this->cacheEnabled = $config['cache_enable'] ?? true;
        $this->debug        = $config['debug'] ?? false;
    }

    /**
     * Rend un template .phtml pour un block donné.
     *
     * @param string         $template  ex: "blocks/header.phtml"
     * @param BlockInterface $block     Le block courant (accessible via $block dans le .phtml)
     * @param string|null    $module    Module source du template (optionnel)
     */
    public function render(string $template, BlockInterface $block, string $module = null): string
    {
        try {
            $path = $this->resolver->resolve($template, $module);
        } catch (\Throwable $e) {
            return "<!-- RESOLVER ERROR: " . htmlspecialchars($e->getMessage()) . " -->";
        }

        if ($this->cacheEnabled) {
            return $this->renderCached($path, $block);
        }

        return $this->renderFile($path, $block);
    }

    /**
     * Rendu avec cache PHP compilé.
     */
    private function renderCached(string $path, BlockInterface $block): string
    {
        $cachedPath = $this->cache->getCachedPath($path);

        // Si le cache est absent ou périmé, on (re)compile
        if (!$this->cache->isValid($path)) {
            $this->cache->compile($path);
        }

        return $this->renderFile($cachedPath, $block);
    }

    /**
     * Rendu isolé du fichier .phtml.
     * La closure garantit que seul $block est exposé au template.
     */
    private function renderFile(string $path, BlockInterface $block): string
    {
        if (!file_exists($path)) {
            return "<!-- TEMPLATE FILE NOT FOUND: {$path} -->";
        }

        // Isolation du scope : seul $block est disponible dans le template
        $renderer = static function (string $_path, BlockInterface $block): string {
            ob_start();
            try {
                include $_path;
                return ob_get_clean();
            } catch (\Throwable $e) {
                ob_end_clean();
                return "<!-- INCLUDE ERROR: " . htmlspecialchars($e->getMessage()) . " -->";
            }
        };

        if ($this->debug) {
            return "<!-- BEGIN: {$path} -->\n"
                 . $renderer($path, $block)
                 . "<!-- END: {$path} -->\n";
        }

        return $renderer($path, $block);
    }

    public function getResolver(): TemplateResolver
    {
        return $this->resolver;
    }
}