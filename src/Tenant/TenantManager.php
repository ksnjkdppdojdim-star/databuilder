<?php

namespace DataBuilder\Tenant;

/**
 * Gestionnaire central des tenants.
 * Résout le tenant actif et expose sa config isolée.
 */
class TenantManager
{
    private ?TenantConfig $activeTenant = null;
    private TenantResolver $resolver;
    private string $tenantsPath;

    public function __construct(array $config)
    {
        $this->tenantsPath = $config['base_path'] . '/tenants';
        $this->resolver    = new TenantResolver($this->tenantsPath);

        $this->boot($config['tenant'] ?? '');
    }

    private function boot(string $forcedTenant): void
    {
        $tenantId = $this->resolver->resolve($forcedTenant);

        if ($tenantId === null) return; // Pas de tenant → comportement global

        $tenantPath = $this->tenantsPath . '/' . $tenantId;
        $config     = new TenantConfig($tenantPath);
        $config->load();

        if (!$config->isActive()) return;

        $this->activeTenant = $config;
    }

    public function isActive(): bool
    {
        return $this->activeTenant !== null;
    }

    public function getActiveTenant(): ?TenantConfig
    {
        return $this->activeTenant;
    }

    /**
     * Retourne le thème du tenant actif, ou le thème global en fallback.
     */
    public function resolveTheme(string $globalTheme): string
    {
        return $this->activeTenant?->getTheme() ?? $globalTheme;
    }

    /**
     * Retourne le chemin des layouts du tenant, ou null si pas de tenant.
     */
    public function getLayoutsPath(): ?string
    {
        if (!$this->isActive()) return null;
        $path = $this->tenantsPath . '/' . $this->activeTenant->getId() . '/layouts';
        return is_dir($path) ? $path : null;
    }

    /**
     * Retourne le chemin des templates du tenant, ou null si pas de tenant.
     */
    public function getTemplatesPath(): ?string
    {
        if (!$this->isActive()) return null;
        $path = $this->tenantsPath . '/' . $this->activeTenant->getId() . '/templates';
        return is_dir($path) ? $path : null;
    }
}