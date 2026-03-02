<?php

namespace DataBuilder\Tenant;

/**
 * Configuration d'un tenant (client isolé).
 * Chaque tenant peut avoir son propre thème, layouts et templates.
 *
 * Structure attendue :
 * tenants/
 *   client_abc/
 *     tenant.xml
 *     themes/
 *     layouts/
 *     templates/
 */
class TenantConfig
{
    private array $data = [];
    private bool $loaded = false;

    public function __construct(private string $tenantPath) {}

    public function load(): void
    {
        if ($this->loaded) return;

        $file = $this->tenantPath . '/tenant.xml';

        if (!file_exists($file)) {
            throw new \RuntimeException("tenant.xml not found in: [{$this->tenantPath}]");
        }

        $xml = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($xml === false) {
            throw new \RuntimeException("Failed to parse tenant.xml in: [{$this->tenantPath}]");
        }

        $this->data = [
            'id'          => (string)($xml->id          ?? ''),
            'name'        => (string)($xml->name        ?? ''),
            'theme'       => (string)($xml->theme       ?? 'base'),
            'active'      => ((string)($xml->active     ?? 'true')) === 'true',
            'domain'      => (string)($xml->domain      ?? ''),
        ];

        $this->loaded = true;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->load();
        return $this->data[$key] ?? $default;
    }

    public function getId(): string     { return $this->get('id', ''); }
    public function getName(): string   { return $this->get('name', ''); }
    public function getTheme(): string  { return $this->get('theme', 'base'); }
    public function isActive(): bool    { return $this->get('active', true); }
    public function getDomain(): string { return $this->get('domain', ''); }
    public function all(): array        { $this->load(); return $this->data; }
}