# DataBuilderIMSCPPlugin

Magento-like templating system plugin for i-MSCP.

## Description

This plugin provides a modular templating system for i-MSCP, featuring:
- Block-based layout system (like Magento)
- Flexible theming (base/custom)
- Template inheritance and overriding
- Multi-tenant support
- Cache management
- Module system for extensibility

## Requirements

- i-MSCP 1.5.0 or higher
- PHP 7.4 or higher
- PHP extensions: pdo, json, mbstring, xml

## Installation

1. Copy the `DataBuilderIMSCPPlugin` folder to your i-MSCP plugins directory:
   
```
   cp -r DataBuilderIMSCPPlugin /var/www/imscp/gui/plugins/
   
```

2. Install the plugin through the i-MSCP interface:
   - Login as administrator
   - Go to Plugins > Plugin management
   - Find "DataBuilderIMSCPPlugin" and click Install
   - Enable the plugin

## Configuration

After installation, you can configure the plugin through:
- Admin panel: Settings > DataBuilder

### Available Configuration Options

- **Theme**: Select default theme (base/custom)
- **Cache**: Enable/disable template caching
- **Debug mode**: Enable for development
- **Enable for clients**: Allow clients to use DataBuilder templates
- **Enable for resellers**: Allow resellers to use DataBuilder templates

## Usage

### As a Template Engine

Other plugins can use DataBuilder as their template engine:

```
php
// Get the DataBuilder plugin instance
$pluginManager = iMSCP_Registry::get('pluginManager');
$databuilder = $pluginManager->pluginGet('DataBuilderIMSCPPlugin');

// Render a template
$html = $databuilder->render('module::controller/action', $data);
```

### Via HTTP Endpoint

You can also render templates via HTTP:

```
/shared/databuilder/render?template=module::controller/action&format=html
```

## Structure

```
DataBuilderIMSCPPlugin/
├── info.php                 # Plugin metadata
├── DataBuilderIMSCPPlugin.php  # Main plugin class
├── config.php               # Default configuration
├── vendor/
│   └── autoload.php         # PSR-4 autoloader
├── src/                     # DataBuilder source (copy or symlink)
├── themes/                  # Plugin themes
├── frontend/                # Frontend controllers
│   ├── admin/
│   ├── client/
│   └── shared/
├── sql/                     # Database migrations
└── README.md
```

## Development

### Adding New Themes

Create your theme in the themes directory:

```
themes/
├── base/                    # Base theme (fallback)
└── custom/                  # Custom theme (user overrides)
    ├── layout/
    ├── template/
    └── layout.xml
```

### Adding Modules

Place modules in the modules directory:

```
modules/
└── your-module/
    ├── module.xml
    ├── Block/
    ├── Controller/
    ├── layouts/
    └── templates/
```

## License

i-MSCP License - See LICENSE file for details
