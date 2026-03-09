<?php

namespace DataBuilder\Integration\Imscp;

use DataBuilder\Integration\IntegrationInterface;
use DataBuilder\Router\RouterInterface;

class ImscpAdapter implements IntegrationInterface
{
    public function getBasePath(): string
    {
        return '/var/www/imscp/databuilder';
    }

    public function getActiveTheme(): string
    {
        return defined('IMSCP_THEME') ? IMSCP_THEME : 'base';
    }

    public function getGlobalData(): array
    {
        return [
            'user'    => $_SESSION['user_logged'] ?? '',
            'domain'  => $_SERVER['HTTP_HOST']    ?? '',
            'version' => defined('IMSCP_VERSION') ? IMSCP_VERSION : '',
        ];
    }

    public function getRouter(): ?RouterInterface { return null; }
    public function getTenantId(): ?string        { return null; }
}