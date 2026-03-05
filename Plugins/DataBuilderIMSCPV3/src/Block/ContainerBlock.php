<?php

namespace DataBuilder\Block;

/**
 * Block conteneur — ne possède pas de template propre.
 * Il rend uniquement ses enfants, enveloppés dans un tag HTML configurable.
 *
 * Exemple XML :
 *   <container name="header" htmlTag="header" htmlClass="site-header">
 */
class ContainerBlock extends AbstractBlock
{
    private string $htmlTag   = 'div';
    private string $htmlClass = '';
    private string $htmlId    = '';

    public function __construct(string $name, string $htmlTag = 'div', string $htmlClass = '', string $htmlId = '')
    {
        parent::__construct($name, ''); // Pas de template
        $this->htmlTag   = $htmlTag;
        $this->htmlClass = $htmlClass;
        $this->htmlId    = $htmlId;
    }

    public function render(): string
    {
        $attrs  = '';
        if ($this->htmlClass) $attrs .= ' class="' . htmlspecialchars($this->htmlClass) . '"';
        if ($this->htmlId)    $attrs .= ' id="'    . htmlspecialchars($this->htmlId)    . '"';

        $inner = $this->renderChildren();

        // Container vide → on n'émet rien pour ne pas polluer le HTML
        if (trim($inner) === '') return '';

        return "<{$this->htmlTag}{$attrs}>{$inner}</{$this->htmlTag}>\n";
    }

    public function setHtmlTag(string $tag): void   { $this->htmlTag   = $tag; }
    public function setHtmlClass(string $class): void { $this->htmlClass = $class; }
    public function setHtmlId(string $id): void     { $this->htmlId    = $id; }

    public function getHtmlTag(): string   { return $this->htmlTag; }
    public function getHtmlClass(): string { return $this->htmlClass; }
    public function getHtmlId(): string    { return $this->htmlId; }
}