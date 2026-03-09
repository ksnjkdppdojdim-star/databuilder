<?php

namespace DataBuilder\Block;

interface BlockInterface
{
    /**
     * Retourne le HTML rendu du block
     */
    public function render(): string;

    /**
     * Nom unique du block (ex: "site.header")
     */
    public function getName(): string;

    /**
     * Template associé au block (ex: "blocks/header.phtml")
     */
    public function getTemplate(): string;

    public function setTemplate(string $template): void;

    /**
     * Données locales du block
     */
    public function setData(string $key, mixed $value): void;
    public function getData(string $key, mixed $default = null): mixed;
    public function getAllData(): array;

    /**
     * Blocks enfants imbriqués
     */
    public function addChild(BlockInterface $block, string $alias = null): void;
    public function getChild(string $alias): ?BlockInterface;
    public function getChildren(): array;

    public function setLayout(array $layoutNode): void;
}