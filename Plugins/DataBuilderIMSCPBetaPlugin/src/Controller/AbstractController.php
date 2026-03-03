<?php

namespace DataBuilder\Controller;

use DataBuilder\Core\Registry;
use DataBuilder\Layout\LayoutManager;
use DataBuilder\Block\BlockFactory;
use DataBuilder\Template\TemplateEngine;
use DataBuilder\Router\Route;

abstract class AbstractController
{
    protected Route $route;
    protected Registry $registry;
    protected LayoutManager $layoutManager;
    protected BlockFactory $blockFactory;
    protected TemplateEngine $templateEngine;

    public function __construct(
        Route          $route,
        Registry       $registry,
        LayoutManager  $layoutManager,
        BlockFactory   $blockFactory,
        TemplateEngine $templateEngine
    ) {
        $this->route          = $route;
        $this->registry       = $registry;
        $this->layoutManager  = $layoutManager;
        $this->blockFactory   = $blockFactory;
        $this->templateEngine = $templateEngine;
    }

    /**
     * Charge le layout associé au handle de la route courante.
     * Retourne le block racine prêt à être rendu.
     */
    protected function loadLayout(?string $handleOverride = null): \DataBuilder\Block\BlockInterface
    {
        $handle = $handleOverride ?? $this->route->getHandle();
        $tree   = $this->layoutManager->getLayout($handle);
        return $this->blockFactory->createFromNode($tree);
    }

    /**
     * Raccourci : charge le layout et retourne le HTML final.
     */
    protected function renderLayout(?string $handleOverride = null): string
    {
        return $this->loadLayout($handleOverride)->render();
    }

    /**
     * Toute action d'un controller doit retourner une string HTML.
     */
    abstract public function execute(): string;
}