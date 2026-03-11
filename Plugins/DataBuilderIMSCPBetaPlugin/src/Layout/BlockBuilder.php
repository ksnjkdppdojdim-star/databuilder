<?php

namespace DataBuilder\Layout;

use DataBuilder\Block\AbstractBlock;
use DataBuilder\Core\Registry;

/**
 * Convertit la structure XML parsée en objets Block instanciés.
 * Gère l'instantiation des classes et la construction de la hiérarchie.
 */
class BlockBuilder
{
    private $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Construit un arbre de blocks à partir des données XML parsées.
     * 
     * @param array $layoutData Structure XML parsée
     * @return AbstractBlock|null Block root instancié
     */
    public function build(array $layoutData): ?AbstractBlock
    {
        if (empty($layoutData)) {
            return null;
        }

        // Si ce n'est pas un block (pas de 'class'), on le ignore
        if (!isset($layoutData['attributes']['class'])) {
            // Cherche le premier enfant qui est un block
            if (isset($layoutData['children']) && is_array($layoutData['children'])) {
                foreach ($layoutData['children'] as $child) {
                    $block = $this->build($child);
                    if ($block) {
                        return $block;
                    }
                }
            }
            return null;
        }

        // Crée l'instance du block
        $className = $layoutData['attributes']['class'];
        $block = $this->instantiateBlock($className, $layoutData['attributes']);

        // Ajoute les enfants
        if (isset($layoutData['children']) && is_array($layoutData['children'])) {
            foreach ($layoutData['children'] as $childData) {
                $childBlock = $this->build($childData);
                if ($childBlock) {
                    $childName = $childData['attributes']['name'] ?? 'unnamed';
                    $block->addChild($childBlock, $childName);
                }
            }
        }

        return $block;
    }

    /**
     * Instancie une classe de block avec ses attributs.
     * 
     * @param string $className Nom complet de la classe
     * @param array $attributes Attributs du block (template, name, etc.)
     * @return AbstractBlock
     * @throws \RuntimeException
     */
    private function instantiateBlock(string $className, array $attributes): AbstractBlock
    {
        if (!class_exists($className)) {
            throw new \RuntimeException("Block class not found: {$className}");
        }

        // Récupère le nom du block
        $blockName = $attributes['name'] ?? 'unnamed';

        // Essaie d'instancier avec les bons paramètres
        $block = null;
        
        // Si c'est un ContainerBlock avec htmlTag, htmlClass, htmlId
        if ($className === 'DataBuilder\Block\ContainerBlock') {
            $htmlTag = $attributes['htmlTag'] ?? 'div';
            $htmlClass = $attributes['htmlClass'] ?? '';
            $htmlId = $attributes['htmlId'] ?? '';
            $block = new $className($blockName, $htmlTag, $htmlClass, $htmlId);
        } else {
            // Pour les autres blocks, essaie avec le constructeur standard
            try {
                // Si la classe accepte $name
                $block = new $className($blockName, $attributes['template'] ?? '');
            } catch (\TypeError $e) {
                // Sinon sans paramètres
                $block = new $className();
            }
        }

        // Configure le template si disponible
        if (isset($attributes['template']) && method_exists($block, 'setTemplate')) {
            $block->setTemplate($attributes['template']);
        }

        // Applique les autres attributs comme données
        foreach ($attributes as $key => $value) {
            if (!in_array($key, ['class', 'name', 'template', 'htmlTag', 'htmlClass', 'htmlId'])) {
                if (method_exists($block, 'setData')) {
                    $block->setData($key, $value);
                }
            }
        }

        return $block;
    }
}
