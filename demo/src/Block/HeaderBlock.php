<?php
namespace DataBuilderDemo\Block;

use DataBuilder\Block\AbstractBlock;

class HeaderBlock extends AbstractBlock
{
    public function __construct(string $name, string $template = '')
    {
        parent::__construct($name, $template);
        $this->setData('site_title', 'DataBuilder');
    }
}