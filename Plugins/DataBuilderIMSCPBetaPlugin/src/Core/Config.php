<?php

namespace DataBuilder\Core;

/**
 * Chargement et accès à la configuration globale de DataBuilder.
 * Source principale : config/databuilder.xml
 * Peut être surchargée par des valeurs passées au runtime (Engine::create([...]))
 */
class Config
{
    private array $data = [];
    private bool $loaded = false;
    private string $configPath;

    public function __construct(string $configPath)
    {
        $this->configPath = $configPath;
    }

    /**
     * Charge la config depuis databuilder.xml + merge avec les overrides runtime
     */
    public function load(array $runtimeOverrides = []): void
    {
        if ($this->loaded) return;

        $xmlFile = $this->configPath . '/databuilder.xml';

        if (file_exists($xmlFile)) {
            $this->data = $this->parseXml($xmlFile);
        }

        // Les overrides runtime (passés à Engine::create()) ont priorité absolue
        $this->data = array_merge($this->data, $runtimeOverrides);

        $this->loaded = true;
    }

    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function all(): array
    {
        return $this->data;
    }

    /**
     * Parse databuilder.xml → tableau PHP plat
     *
     * Exemple XML attendu :
     * <config>
     *     <theme>custom</theme>
     *     <cache_enable>true</cache_enable>
     *     <debug>false</debug>
     * </config>
     */
    private function parseXml(string $filepath): array
    {
        $xml = simplexml_load_file($filepath, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($xml === false) {
            throw new \RuntimeException("Failed to parse config file: {$filepath}");
        }

        $result = [];

        foreach ($xml->children() as $key => $value) {
            $result[(string)$key] = $this->castValue((string)$value);
        }

        return $result;
    }

    /**
     * Cast automatique des valeurs XML vers les types PHP natifs
     */
    private function castValue(string $value)
    {
        if ($value === 'true')  return true;
        if ($value === 'false') return false;
        if ($value === 'null')  return null;
        if (is_numeric($value)) return str_contains($value, '.') ? (float)$value : (int)$value;
        return $value;
    }
}