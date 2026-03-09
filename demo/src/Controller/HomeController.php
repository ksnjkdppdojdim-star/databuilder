<?php
namespace DataBuilderDemo\Controller;

use DataBuilder\Core\Engine;
use DataBuilder\Controller\AbstractController;


class HomeController extends AbstractController
{
    public function execute(): string
    {
        return $this->renderLayout();
    }

    public function index(): string
    {
        $root = $this->loadLayout('homepage');

        // Injecte des données dans les blocks via le registry
        $header = $this->blockFactory->getInstance('site.header');
        $header?->setData('site_title', '🚀 DataBuilder Demo');

        $content = $this->blockFactory->getInstance('page.content');
        $content?->setData('heading', 'Moteur de templating modulaire');
        $content?->setData('text', 'Layout XML fusionné, fallback de thème, blocks modulaires — tout fonctionne.');

        return $root->render();
    }
}