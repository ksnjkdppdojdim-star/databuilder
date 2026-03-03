<?php
// modules/example-module/Controller/HomeController.php

namespace DataBuilder\Controller;

class HomeController extends AbstractController
{
    public function execute(): string
    {
        return $this->renderLayout(); // charge homepage handle
    }

    public function index(): string
    {
        $root = $this->loadLayout('homepage');

        // Injecte des données dans un block spécifique via le registry
        $header = $this->blockFactory->getInstance('site.header');
        $header?->setData('site_title', 'Welcome to DataBuilder');

        return $root->render();
    }
}