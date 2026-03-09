<?php

namespace DataBuilder\Integration;

use DataBuilder\Router\RouterInterface;

interface IntegrationInterface
{
    public function getBasePath(): string;
    public function getActiveTheme(): string;
    public function getGlobalData(): array;
    public function getRouter(): ?RouterInterface;

    /**
     * ID du tenant actif (optionnel — pour hosting multi-client)
     */
    public function getTenantId(): ?string;
}