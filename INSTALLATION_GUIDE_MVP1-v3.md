# DataBuilder MVP1-v3 Installation Guide

## What Was Fixed

This version (v3) contains critical architectural corrections to MVP1:

### ✅ Fixed Issues from v2:

1. **CSS/JS Not Loading** 
   - **Problem**: DataBuilder was returning complete HTML pages directly to browser
   - **Solution**: Now uses iMSCP event hooks to properly integrate into wrapper
   - **File**: `Plugins/DataBuilderIMSCPBetaPlugin/asset-serve.php` (secure asset endpoint)

2. **Variables Displaying Textually** (`{ADMIN_USERS}`, `{DOMAINS}`, etc.)
   - **Problem**: iMSCP needs to process HTML to replace variables
   - **Solution**: DataBuilder now returns CONTENT ONLY, iMSCP handles replacement
   - **File**: `src/Integration/ImscpBridge.php` (access iMSCP variables)

3. **No Design/Styling**
   - **Problem**: CSS file not loaded due to architecture flaw
   - **Solution**: CSS injected via secure endpoint + iMSCP CSS/JS loading
   - **File**: `themes/default/css/databuilder.css` (850+ lines, responsive)

4. **Class Not Found Error on Uninstall**
   - **Problem**: Autoloader had PSR-4 path separator issues
   - **Solution**: Fixed autoloader in plugin main class
   - **File**: `Plugins/DataBuilderIMSCPBetaPlugin/DataBuilderIMSCPBetaPlugin.php`

### 📦 New/Modified Files in v3:

```
✓ src/Block/Admin/IndexBlock.php          (REPLACED - uses ImscpBridge)
✓ src/Integration/ImscpBridge.php         (NEW - access iMSCP variables)
✓ src/Core/Engine.php                     (UPDATED - renderPageContent method)
✓ src/Cleanup/CleanupManager.php          (NEW - complete uninstallation)
✓ Plugins/DataBuilderIMSCPBetaPlugin/...  (UPDATED - event hooks)
✓ Plugins/DataBuilderIMSCPBetaPlugin/asset-serve.php  (NEW - secure assets)
✓ themes/default/css/databuilder.css      (EXISTING - responsive design)
✓ themes/default/js/databuilder.js        (EXISTING - interactivity)
✓ themes/default/layouts/admin_index.xml  (EXISTING - block hierarchy)
✓ themes/default/templates/admin/index.phtml (EXISTING - template)
```

---

## Installation Steps

### Step 1: Upload to iMSCP

1. Go to **iMSCP Admin Panel** → **Plugins**
2. Click **Upload a plugin**
3. Select file: `DataBuilderMVP1-v3.zip` (from your Documents folder)
4. Click **Upload**

### Step 2: Install & Enable

1. Find **DataBuilderIMSCPBeta** in plugin list
2. Click **Install** button
3. Wait for success message
4. Click **Enable** button

### Step 3: Test the Installation

Navigate to: `http://your-imscp-domain/admin/index`

You should see:
- ✅ Admin dashboard with **design/styling** applied
- ✅ **Statistics cards** in a grid layout (not horizontal)
- ✅ **Numbers displayed** (not `{ADMIN_USERS}`, `{DOMAINS}`, etc.)
- ✅ **Dark mode support** (if iMSCP has dark mode enabled)
- ✅ **Responsive design** (test at different screen widths)

---

## Debugging (If Issues)

### Launch Debug Commands

Press **F12** in your browser to open Developer Tools.

Go to **Console** tab and run:

```javascript
// Check if DataBuilder is loaded
window.DataBuilderDebug.getStats()

// Output should show:
// {
//   loaded: true,
//   cssLoaded: true,
//   jsLoaded: true,
//   version: "MVP1-v3"
// }
```

### Check CSS/JS Loading

Go to **Network** tab (F12):

1. **Refresh** the page (Ctrl+R)
2. Look for requests to:
   - `asset-serve.php?name=databuilder.css` (Status: **200**)
   - `asset-serve.php?name=databuilder.js` (Status: **200**)

3. If Status is **403** or **404**: CSS/JS endpoint blocked
4. If not listed: CSS/JS never requested

### Check Variable Replacement

Go to **Elements** tab (F12):

1. Right-click on page → **Inspect**
2. Look for `<span>12</span>` or similar
3. Should NOT see `{ADMIN_USERS}`, `{DOMAINS}`, etc.
4. If you see variables textually: iMSCP template engine not active

### Check CSS Applied

In **Console** tab (F12):

```javascript
// Get DataBuilder CSS color variables
window.DataBuilderDebug.getColors()

// Output should show CSS variables from databuilder.css
```

### Test Responsive Design

1. Press F12 → **Device Toolbar** (Ctrl+Shift+M)
2. Set width to **768px**
3. Layout should adjust (columns reduce)
4. Cards should stack vertically on mobile

---

## What's Different in v3

### Architecture Change: From Page Rendering to Content Injection

**v2 (Old - Broken):**
```
Browser → DataBuilder returns HTML page → CSS/JS never loaded
         → iMSCP wrapper bypassed → Variables never replaced
```

**v3 (New - Fixed):**
```
Browser → iMSCP wrapper renders
         → Event hook: onAfterBuildTemplate
         → DataBuilder injects CONTENT into {LAYOUT_CONTENT}
         → Event hook: onAfterLoadTemplateFile  
         → DataBuilder injects CSS/JS via asset-serve.php
         → iMSCP replaces ALL variables
         → iMSCP CSS/JS loads
         → Final HTML with design + variables + functionality
```

### Key Components

**ImscpBridge.php** - Access iMSCP Variables Without Database

```php
$adminUsers = \DataBuilder\Integration\ImscpBridge::getVariable('ADMIN_USERS');
// Gets value from iMSCP's template assignments (no DB query)
```

**asset-serve.php** - Secure Asset Delivery

```php
// Old way (broken):
<link href="themes/default/css/databuilder.css">

// New way (fixed):
<script src="asset-serve.php?name=databuilder.css"></script>
// Whitelisted, verified, secure endpoint
```

**Event Hooks** - Proper Integration

```php
// Hook into iMSCP's template building
iMSCP_Events_Manager::getInstance()->attach(
    'onAfterBuildTemplate',
    function(iMSCP_Events_Event $event) {
        // Inject DataBuilder content here
    }
);
```

---

## Uninstall Steps

1. Go to **iMSCP Admin Panel** → **Plugins**
2. Find **DataBuilderIMSCPBeta**
3. Click **Uninstall**
4. System will:
   - Remove all DataBuilder directories
   - Clear all DataBuilder caches
   - Remove plugin files
   - Clean up completely

No leftover files or broken references.

---

## Support & Details

For detailed architecture explanation, see: `FIXES_MVP1.md`
For original implementation details, see: `DEBUG_MVP1.md`

---

## Version History

- **v1**: Initial concept (not implemented)
- **v2**: First MVP (CSS/JS not loaded, variables textual, uninstall error)
- **v3**: Critical fixes (event hooks, ImscpBridge, asset-serve, autoloader fix)

---

**Last Updated**: 2025-03-05
**Status**: Ready for Testing
**Next Phase**: MVP2 (Additional Admin Pages)
