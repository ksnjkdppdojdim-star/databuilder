<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

use DataBuilder\Core\Engine;
use DataBuilder\Layout\LayoutManager;

$engine = Engine::create([
    'base_path'    => dirname(__DIR__),
    'theme'        => 'custom',
    'cache_enable' => false,
    'debug'        => true,
]);

// Debug — affiche les chemins résolus avant dispatch
$config = $engine->getConfig();
echo '<pre>';
echo "themes_path : " . $config['themes_path'] . "\n";
echo "modules_path: " . $config['modules_path'] . "\n";
echo "config_path : " . $config['config_path'] . "\n";
echo "theme actif : " . $config['theme'] . "\n";

// Vérifie si les fichiers existent
$files = [
    $config['themes_path'] . '/base/layouts/default.xml',
    $config['themes_path'] . '/custom/layouts/homepage.xml',
    $config['config_path'] . '/routes.xml',
];

foreach ($files as $f) {
    echo file_exists($f) ? "✅ $f\n" : "❌ MANQUANT: $f\n";
}
echo '</pre>';
