<?php
// src/Event/BlockEvent.php

namespace DataBuilder\Event;

use DataBuilder\Block\BlockInterface;

/**
 * Événements liés au cycle de vie d'un block.
 *
 * Noms standards :
 *   block.render.before   → avant le rendu d'un block
 *   block.render.after    → après le rendu, reçoit le HTML produit
 *   block.create          → après l'instanciation par BlockFactory
 */
class BlockEvent extends AbstractEvent
{
    private $html = '';
    private $block;

    public function __construct(string $name, BlockInterface $block)
    {
        parent::__construct($name);
        $this->block = $block;
    }

    public function getBlock(): BlockInterface { return $this->block; }

    // Pour block.render.after : accès au HTML produit
    public function setHtml(string $html): void { $this->html = $html; }
    public function getHtml(): string           { return $this->html; }
}