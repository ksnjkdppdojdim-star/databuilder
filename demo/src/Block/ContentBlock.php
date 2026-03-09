<?php
namespace DataBuilderDemo\Block;

use DataBuilder\Block\AbstractBlock;


class ContentBlock extends AbstractBlock
{
    public function __construct(string $name, string $template = '')
    {
        parent::__construct($name, $template);
        $this->setData('heading', 'Bienvenue sur DataBuilder');
        $this->setData('text', 'Le moteur de templating modulaire inspiré de Magento.');
    }
}