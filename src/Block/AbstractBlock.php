<?php

namespace DataBuilder\Block;

use DataBuilder\Template\TemplateEngine;

abstract class AbstractBlock implements BlockInterface
{
    protected string $name     = '';
    protected string $template = '';
    protected array  $data     = [];
    protected array  $children = []; // alias => BlockInterface
    protected array  $layoutNode = [];
    protected ?TemplateEngine $templateEngine = null;

    public function __construct(string $name, string $template = '')
    {
        $this->name     = $name;
        $this->template = $template;
    }

    // ─── Render ──────────────────────────────────────────────────────────────

    public function render(): string
    {
        if ($this->templateEngine === null) {
            throw new \RuntimeException(
                "TemplateEngine not injected in block [{$this->name}]. " .
                "Use setTemplateEngine() before render()."
            );
        }

        if (empty($this->template)) {
            // Block sans template → rend uniquement ses enfants
            return $this->renderChildren();
        }

        return $this->templateEngine->render($this->template, $this);
    }

    /**
     * Rend tous les enfants dans l'ordre et retourne le HTML concatené.
     * Appelable depuis un .phtml via $block->renderChildren()
     */
    public function renderChildren(): string
    {
        $html = '';
        foreach ($this->children as $child) {
            $html .= $child->render();
        }
        return $html;
    }

    /**
     * Rend un enfant spécifique par alias.
     * Appelable depuis un .phtml via $block->renderChild('sidebar')
     */
    public function renderChild(string $alias): string
    {
        $child = $this->getChild($alias);
        return $child ? $child->render() : '';
    }

    // ─── Identity ────────────────────────────────────────────────────────────

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    // ─── Template ────────────────────────────────────────────────────────────

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    public function setTemplateEngine(TemplateEngine $engine): void
    {
        $this->templateEngine = $engine;

        // Propage aux enfants
        foreach ($this->children as $child) {
            if ($child instanceof AbstractBlock) {
                $child->setTemplateEngine($engine);
            }
        }
    }

    // ─── Data ─────────────────────────────────────────────────────────────────

    public function setData(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function getData(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function getAllData(): array
    {
        return $this->data;
    }

    /**
     * Bulk assign — utile pour injecter un tableau depuis un data provider
     */
    public function assignData(array $data): void
    {
        $this->data = array_merge($this->data, $data);
    }

    // ─── Children ────────────────────────────────────────────────────────────

    public function addChild(BlockInterface $block, string $alias = null): void
    {
        $alias = $alias ?? $block->getName();
        $this->children[$alias] = $block;
    }

    public function getChild(string $alias): ?BlockInterface
    {
        return $this->children[$alias] ?? null;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function removeChild(string $alias): void
    {
        unset($this->children[$alias]);
    }

    // ─── Layout node ─────────────────────────────────────────────────────────

    public function setLayout(array $layoutNode): void
    {
        $this->layoutNode = $layoutNode;
    }

    public function getLayoutNode(): array
    {
        return $this->layoutNode;
    }

    // ─── Helpers pratiques pour les .phtml ───────────────────────────────────

    /**
     * Escape HTML — à utiliser dans les templates pour sécuriser les outputs
     * Usage dans .phtml : <?= $block->e($block->getData('title')) ?>
     */
    public function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Vérifie si un enfant existe
     */
    public function hasChild(string $alias): bool
    {
        return isset($this->children[$alias]);
    }
}