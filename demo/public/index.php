<?php

// vendor/ est à la racine du projet, pas dans demo/
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use DataBuilder\Core\Engine;

$engine = Engine::create([
    'base_path'    => dirname(__DIR__),
    'theme'        => 'custom',
    'cache_enable' => false,
    'debug'        => true,
]);

$engine->dispatch();