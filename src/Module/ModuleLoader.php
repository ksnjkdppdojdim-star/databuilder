<?php

namespace DataBuilder\Module;

/**
 * Charge les modules déclarés dans config/modules.xml
 * et enregistre leurs contributions (routes, layouts, blocks).
 *
 * Format modules.xml :
 * <modules>
 *     <module name="example-module" active="true" />
 * </modules>
 */
class ModuleLoader
{
    private array $modules = [];

    public function __construct(private array $config) {}

    public function load(): void
    {
        $file = $this->config['config_path'] . '/modules.xml';
        if (!file_exists($file)) return;

        $xml = simplexml_load_file($file);
        if ($xml === false) return;

        foreach ($xml->module as $module) {
            $name   = (string)($module['name']   ?? '');
            $active = ((string)($module['active'] ?? 'true')) === 'true';

            if ($name === '' || !$active) continue;

            $modulePath = $this->config['modules_path'] . '/' . $name;
            if (!is_dir($modulePath)) continue;

            $this->modules[$name] = $this->parseModuleXml($modulePath, $name);
        }
    }

    private function parseModuleXml(string $path, string $name): array
    {
        $file = $path . '/module.xml';
        if (!file_exists($file)) return ['name' => $name, 'path' => $path];

        $xml = simplexml_load_file($file);
        if ($xml === false) return ['name' => $name, 'path' => $path];

        return [
            'name'    => $name,
            'path'    => $path,
            'version' => (string)($xml->version ?? '1.0.0'),
            'author'  => (string)($xml->author  ?? ''),
        ];
    }

    public function getModules(): array  { return $this->modules; }
    public function isLoaded(string $name): bool { return isset($this->modules[$name]); }

    /**
     * Retourne les chemins de layouts de tous les modules actifs pour un handle.
     */
    public function resolveLayoutFiles(string $handle): array
    {
        $files = [];
        foreach ($this->modules as $module) {
            $path = $module['path'] . '/layouts/' . $handle . '.xml';
            if (file_exists($path)) $files[] = $path;

            $default = $module['path'] . '/layouts/default.xml';
            if ($handle !== 'default' && file_exists($default)) $files[] = $default;
        }
        return $files;
    }
}