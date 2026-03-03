<?php

namespace DataBuilder\Controller;

use DataBuilder\Router\Router;
use DataBuilder\Router\Route;
use DataBuilder\Layout\LayoutManager;
use DataBuilder\Block\BlockFactory;
use DataBuilder\Template\TemplateEngine;
use DataBuilder\Core\Registry;

/**
 * Point central du dispatch.
 * Reçoit l'URI, résout la route, instancie le controller, exécute l'action, envoie la réponse.
 */
class FrontController
{
    private BlockFactory $blockFactory;

    public function __construct(
        private Router         $router,
        private LayoutManager  $layoutManager,
        private TemplateEngine $templateEngine,
        private Registry       $registry
    ) {
        $this->blockFactory = new BlockFactory($this->templateEngine);
    }

    public function dispatch(string $uri): void
    {
        try {
            $route = $this->router->match($uri);

            // Aucune route → 404
            if ($route === null) {
                $this->send404();
                return;
            }

            $html = $this->runController($route);

            $this->sendResponse($html);

        } catch (\Throwable $e) {
            $this->send500($e);
        }
    }

    // ─── Exécution du controller ──────────────────────────────────────────────

    private function runController(Route $route): string
    {
        $class  = $route->getController();
        $action = $route->getAction();

        if (!class_exists($class)) {
            throw new \RuntimeException("Controller class not found: [{$class}]");
        }

        $controller = new $class(
            $route,
            $this->registry,
            $this->layoutManager,
            $this->blockFactory,
            $this->templateEngine
        );

        if (!$controller instanceof AbstractController) {
            throw new \RuntimeException("Controller [{$class}] must extend AbstractController");
        }

        if (!method_exists($controller, $action)) {
            throw new \RuntimeException("Action [{$action}] not found in [{$class}]");
        }

        // Permet aux controllers d'avoir une action spécifique (login, show...)
        // tout en gardant execute() comme point d'entrée standard
        if ($action !== 'execute') {
            return $controller->{$action}();
        }

        return $controller->execute();
    }

    // ─── Réponses HTTP ────────────────────────────────────────────────────────

    private function sendResponse(string $html, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=UTF-8');
        echo $html;
    }

    private function send404(): void
    {
        http_response_code(404);
        header('Content-Type: text/html; charset=UTF-8');
        echo $this->render404();
    }

    private function send500(\Throwable $e): void
    {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
        echo $this->render500($e);
    }

    private function render404(): string
    {
        // Tente de charger un layout 404 personnalisé, sinon fallback minimal
        try {
            $tree = $this->layoutManager->getLayout('404');
            return $this->blockFactory->createFromNode($tree)->render();
        } catch (\Throwable) {
            return '<h1>404 — Page not found</h1>';
        }
    }

    private function render500(\Throwable $e): string
    {
        try {
            $tree  = $this->layoutManager->getLayout('500');
            $block = $this->blockFactory->createFromNode($tree);
            $block->setData('error_message', $e->getMessage());
            return $block->render();
        } catch (\Throwable) {
            return '<h1>500 — Internal Server Error</h1><p>' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }
}