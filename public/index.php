<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use DataBuilder\Core\Engine;

$engine = Engine::create([
    'base_path'    => dirname(__DIR__),
    'theme'        => 'custom',
    'cache_enable' => false,
    'debug'        => true,
]);

$engine->dispatch();