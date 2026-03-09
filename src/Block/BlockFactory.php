<?php

namespace DataBuilder\Block;

use DataBuilder\Template\TemplateEngine;

use DataBuilder\Event\EventDispatcher;
use DataBuilder\Event\BlockEvent;

/**
 * Instancie les blocks à partir d'un nœud de layout parsé.
 * Supporte l'instanciation lazy : le block n'est créé que si son rendu est requis.
 */
class BlockFactory
{
    private array $instances = []; // Cache des instances déjà créées
    private TemplateEngine $templateEngine;
    private ?EventDispatcher $eventDispatcher = null;
    
    // Setter
    public function setEventDispatcher(EventDispatcher $dispatcher): void
    {
        $this->eventDispatcher = $dispatcher;
    }

    public function __construct(TemplateEngine $templateEngine)
    {
        $this->templateEngine = $templateEngine;
    }

    /**
     * Crée un block à partir d'un nœud de layout parsé.
     *
     * Nœud attendu :
     * [
     *   'tag'        => 'block',
     *   'attributes' => ['name' => 'site.header', 'class' => 'DataBuilder\Block\HeaderBlock', 'template' => 'blocks/header.phtml'],
     *   'children'   => [...]
     * ]
     */
    public function createFromNode(array $node): BlockInterface
    {
        // ✅ Si le nœud racine est <layout>, on descend directement au premier enfant utile
        if ($node['tag'] === 'layout') {
            foreach ($node['children'] as $child) {
                if (in_array($child['tag'], ['block', 'container'])) {
                    return $this->createFromNode($child);
                }
            }
            throw new \RuntimeException("Layout node has no renderable children.");
        }
    
        $attrs = $node['attributes'] ?? [];
        $tag   = $node['tag'];
        $name  = $attrs['name'] ?? uniqid('block_');
    
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }
    
        $block = match($tag) {
            'container' => $this->createContainer($name, $attrs),
            'block'     => $this->createBlock($name, $attrs),
            default     => throw new \RuntimeException("Unknown layout node tag: [{$tag}]")
        };
    
        if ($block instanceof AbstractBlock) {
            $block->setTemplateEngine($this->templateEngine);
            $block->setLayout($node);
            if ($this->eventDispatcher !== null) {
                $block->setEventDispatcher($this->eventDispatcher);
            }
        }
    
        foreach ($node['children'] ?? [] as $childNode) {
            $childTag = $childNode['tag'] ?? '';
            if (in_array($childTag, ['block', 'container'])) {
                $childBlock = $this->createFromNode($childNode);
                $block->addChild($childBlock);
            }
        }
    
        $this->instances[$name] = $block;
        $this->dispatch(new BlockEvent('block.create', $block));
    
        return $block;
    }

    // Méthode privée à ajouter
    private function dispatch(object $event): void
    {
        $this->eventDispatcher?->dispatch($event);
    }

    /**
     * Instancie un block métier via sa classe PHP déclarée dans le XML.
     */
    private function createBlock(string $name, array $attrs): BlockInterface
    {
        $class = $attrs['class'] ?? '';
        
        if ($class === '') {
            throw new \RuntimeException("No class defined for block [{$name}]. Attribute 'class' is required.");
        }


        $template = $attrs['template'] ?? '';

        if (!class_exists($class)) {
            throw new \RuntimeException("Block class not found: [{$class}] for block [{$name}]");
        }

        $block = new $class($name, $template);

        if (!$block instanceof BlockInterface) {
            throw new \RuntimeException("Class [{$class}] must implement BlockInterface");
        }

        return $block;
    }

    /**
     * Instancie un ContainerBlock.
     */
    private function createContainer(string $name, array $attrs): ContainerBlock
    {
        return new ContainerBlock(
            $name,
            $attrs['htmlTag']   ?? 'div',
            $attrs['htmlClass'] ?? '',
            $attrs['htmlId']    ?? ''
        );
    }

    /**
     * Récupère une instance en cache par nom.
     */
    public function getInstance(string $name): ?BlockInterface
    {
        return $this->instances[$name] ?? null;
    }

    /**
     * Vide le cache des instances (utile pour les tests).
     */
    public function flush(): void
    {
        $this->instances = [];
    }
}