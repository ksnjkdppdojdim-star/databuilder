<?php

namespace DataBuilder\Integration\Cpanel;

use DataBuilder\Integration\IntegrationInterface;
use DataBuilder\Router\RouterInterface;

class CpanelAdapter implements IntegrationInterface
{
    public function getBasePath(): string
    {
        return '/usr/local/cpanel/base/databuilder';
    }

    public function getActiveTheme(): string
    {
        return function_exists('cpanel_get_user_theme')
            ? cpanel_get_user_theme() ?? 'base'
            : 'base';
    }

    public function getGlobalData(): array
    {
        return [
            'user'    => function_exists('cpanel_current_user')  ? cpanel_current_user()    : '',
            'domain'  => function_exists('cpanel_primary_domain') ? cpanel_primary_domain() : '',
            'version' => defined('CPANEL_VERSION') ? CPANEL_VERSION : '',
        ];
    }

    public function getRouter(): ?RouterInterface { return null; }
    public function getTenantId(): ?string        { return null; }
}