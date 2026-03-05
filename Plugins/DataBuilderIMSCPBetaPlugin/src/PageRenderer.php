<?php
/**
 * DataBuilder Page Renderer
 * 
 * Renders DataBuilder pages with full UI layout
 * Handles data loading, block rendering, and layout integration
 */

namespace DataBuilder;

use SimpleXMLElement;

class PageRenderer
{
    private string $pluginDir;
    private string $theme;
    private array $page
