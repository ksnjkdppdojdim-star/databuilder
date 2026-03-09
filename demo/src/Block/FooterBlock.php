<?php
namespace DataBuilderDemo\Block;

use DataBuilder\Block\AbstractBlock;


class FooterBlock extends AbstractBlock
{
    public function __construct(string $name, string $template = '')
    {
        parent::__construct($name, $template);
        $this->setData('copyright', '© ' . date('Y') . ' DataBuilder — All rights reserved');
    }
}