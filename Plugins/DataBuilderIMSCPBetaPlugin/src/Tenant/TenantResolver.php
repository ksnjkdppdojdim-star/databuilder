<?php

namespace DataBuilder\Tenant;

/**
 * Résout le tenant actif selon la requête courante.
 * Stratégies supportées :
 *   - Par domaine   : client_abc.example.com
 *   - Par path      : example.com/client_abc/
 *   - Par config    : tenant forcé dans databuilder.xml
 */
class TenantResolver
{
    private $tenantsPath;

    public function __construct(string $tenantsPath)
    {
        $this->tenantsPath = $tenantsPath;
    }

    /**
     * Résout le tenant actif.
     * Retourne l'ID du tenant ou null si aucun tenant trouvé.
     */
    public function resolve(string $forcedTenant = ''): ?string
    {
        // 1. Tenant forcé (depuis config ou runtime)
        if ($forcedTenant !== '') {
            return $this->tenantExists($forcedTenant) ? $forcedTenant : null;
        }

        // 2. Résolution par domaine
        $byDomain = $this->resolveByDomain();
        if ($byDomain !== null) return $byDomain;

        // 3. Résolution par path URI
        $byPath = $this->resolveByPath();
        if ($byPath !== null) return $byPath;

        return null;
    }

    private function resolveByDomain(): ?string
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ($host === '') return null;

        foreach ($this->listTenants() as $tenantId) {
            try {
                $config = new TenantConfig($this->tenantsPath . '/' . $tenantId);
                if ($config->getDomain() === $host) {
                    return $tenantId;
                }
            } catch (\RuntimeException) {
                continue;
            }
        }

        return null;
    }

    private function resolveByPath(): ?string
    {
        $uri     = $_SERVER['REQUEST_URI'] ?? '/';
        $segment = explode('/', trim($uri, '/'))[0] ?? '';

        if ($segment !== '' && $this->tenantExists($segment)) {
            return $segment;
        }

        return null;
    }

    private function tenantExists(string $tenantId): bool
    {
        return is_dir($this->tenantsPath . '/' . $tenantId)
            && file_exists($this->tenantsPath . '/' . $tenantId . '/tenant.xml');
    }

    private function listTenants(): array
    {
        $tenants = [];
        foreach (glob($this->tenantsPath . '/*/tenant.xml') as $file) {
            $tenants[] = basename(dirname($file));
        }
        return $tenants;
    }
}