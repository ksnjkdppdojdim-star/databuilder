# 🎨 DataBuilder iMSCP Plugin - VISUAL PROJECT OVERVIEW

---

## 📊 PHASE FLOW DIAGRAM

```
                          START HERE
                               ↓
                    ┌──────────────────┐
                    │   PHASE 0        │
                    │ Fix Dark Mode    │
                    │    ERROR         │
                    │  (CRITICAL)      │
                    └────────┬─────────┘
                             ↓
              ┌──────────────────────────────┐
              │   PHASE 1                    │
              │ Architecture Core Structure  │
              └────────┬─────────────────────┘
                       ↓
         ┌─────────────────────────┐
         │      PHASE 2            │
         │  Template/Layout/Block  │
         └────────┬────────────────┘
                  ↓
         ┌─────────────────────────┐
         │      PHASE 3            │
         │  Data Separation        │
         └────────┬────────────────┘
                  ↓
         ┌─────────────────────────┐
         │      PHASE 4            │
         │  Page Routing/Fallback  │
         └────────┬────────────────┘
                  ↓
      ✅ FOUNDATION COMPLETE
         
         ┌─────────────────────────┐
         │      PHASE 5            │
         │ Admin Index Page (POC)  │
         │     (FIRST PAGE)        │
         └────────┬────────────────┘
                  ↓
         ┌─────────────────────────┐
         │      PHASE 6            │
         │  Design & Styling       │
         │  + Dark Mode            │
         └────────┬────────────────┘
                  ↓
      ✅ FIRST COMPLETE PAGE
         
         ┌─────────────────────────┐
         │      PHASE 7            │
         │  Installation Hooks     │
         └────────┬────────────────┘
                  ↓
         ┌─────────────────────────┐
         │      PHASE 8            │
         │  Documentation          │
         └────────┬────────────────┘
                  ↓
         ┌─────────────────────────┐
         │      PHASE 9            │
         │  Testing & QA           │
         └────────┬────────────────┘
                  ↓
         ┌─────────────────────────┐
         │     PHASE 10            │
         │ Optimization & Polish   │
         └────────┬────────────────┘
                  ↓
                 ✅ COMPLETE
```

---

## 🏗️ ARCHITECTURE DIAGRAM

```
┌─────────────────────────────────────────────────────────────┐
│                      USER REQUEST                           │
│                   /admin/databuilder                        │
└──────────────────────────┬──────────────────────────────────┘
                           ↓
                    ┌──────────────┐
                    │   IMSCPPAGE  │
                    │  CONTROLLER  │◄──── Checks if DataBuilder
                    │              │      has this page
                    └──────┬───────┘
                           ↓
                ┌──────────────────────┐
                │ DataBuilder Has      │
                │     This Page?       │
                └───────┬──────┬───────┘
                        │      │
            YES          │      │         NO
        ┌───────┐        │      │      ┌───────┐
        ↓       └────────┼──────┘──────┴──→ ↓    
    ┌─────────────────────────┐          ┌──────────┐
    │   DATABUILDER RENDERS   │          │ iMSCP    │
    │  (Layout + Block +      │          │ DEFAULT  │
    │   Template + Data)      │          │ THEME    │
    └────┬────────────────────┘          └──────────┘
         ↓
    ┌─────────────────────────┐
    │  HTML OUTPUT            │
    └────┬────────────────────┘
         ↓
    ┌─────────────────────────┐
    │  STYLED HTML (with CSS) │
    └────┬────────────────────┘
         ↓
    ┌─────────────────────────┐
    │   BROWSER RENDERS       │
    └────┬────────────────────┘
         ↓
    ┌─────────────────────────┐
    │   USER SEES PAGE        │
    └─────────────────────────┘
```

---

## 📁 FILE STRUCTURE OVERVIEW

```
DataBuilderIMSCPBetaPlugin/
│
├── 📋 CONFIGURATION
│   ├── config.php                     ← Plugin settings
│   ├── info.php                       ← Plugin metadata
│   └── setup.bat                      ← Installation script
│
├── 🔧 CORE SYSTEM (src/)
│   ├── Block/                         ← Rendering blocks
│   │   └── Admin/
│   │       ├── IndexBlock.php         ← ⭐ CREATE
│   │       └── ...
│   ├── Controller/
│   │   └── ImscpPageController.php    ← Route handler
│   ├── Core/
│   │   └── Engine.php                 ← Main renderer
│   ├── Layout/
│   │   └── ...                        ← Layout system
│   ├── Router/
│   │   └── ...                        ← Routing logic
│   ├── Template/
│   │   └── ...                        ← Template engine
│   ├── Debug/
│   │   └── Debugger.php               ← ⭐ CREATE
│   └── PageRenderer.php               ← Render handler
│
├── 🎨 THEMES (themes/)
│   ├── index.tpl                      ← Main entry point
│   ├── theme.xml                      ← Theme config
│   │
│   ├── base/                          ← Base theme (core)
│   │   ├── layout.xml
│   │   └── templates/
│   │
│   ├── default/                       ← Default theme
│   │   ├── layouts/
│   │   │   ├── admin.xml              ← ⭐ CREATE
│   │   │   └── admin_index.xml        ← ⭐ CREATE
│   │   ├── templates/
│   │   │   ├── page/
│   │   │   │   ├── layout.phtml       ← ⭐ CREATE
│   │   │   │   ├── header.phtml       ← ⭐ CREATE
│   │   │   │   └── footer.phtml       ← ⭐ CREATE
│   │   │   └── admin/
│   │   │       ├── index.phtml        ← ⭐ CREATE
│   │   │       └── sections/
│   │   ├── data/
│   │   │   └── admin/
│   │   │       └── indexData.xml      ← ⭐ CREATE
│   │   └── assets/
│   │       ├── css/
│   │       │   ├── theme.css          ← ⭐ CREATE
│   │       │   └── dark-mode.css      ← ⭐ CREATE
│   │       └── js/
│   │           ├── app.js             ← ⭐ CREATE
│   │           └── dark-mode.js       ← ⭐ CREATE
│   │
│   └── custom/                        ← User overrides
│       ├── layouts/
│       └── templates/
│
├── 🌐 FRONTEND
│   ├── admin/
│   │   └── databuilder.php            ← Entry point (⚠️ Fix error)
│   └── client/
│
├── 🔌 INTEGRATION
│   ├── IntegrationInterface.php
│   ├── cpanel/
│   └── imscp/
│
├── 📚 DOCUMENTATION (You are here!)
│   ├── README_DOCUMENTATION.md        ← Index of all docs
│   ├── QUICKSTART.md                  ← START HERE
│   ├── IMPLEMENTATION_PLAN.md          ← Full strategy
│   ├── STRUCTURED_TODO.md              ← Daily checklist
│   ├── TODO_TRACKER.json               ← Progress tracking
│   ├── TODO_IMPLEMENTATION.md          ← Technical details
│   ├── TODO_ADMIN_INDEX.md             ← Admin page notes
│   └── NOTES.md                        ← Your notes
│
└── 🧪 TESTS (tests/) - ⭐ TO CREATE
    ├── unit/
    │   ├── CoreEngineTest.php
    │   └── ...
    └── integration/
        ├── PageRenderTest.php
        └── ...
```

---

## 🔄 PAGE RENDERING FLOW

### How a Page Gets Rendered (Simplified)

```
USER REQUESTS: /admin/index
       ↓
ImscpPageController.dispatch('index', 'admin')
       ↓
Check: themes/default/layouts/admin_index.xml exists?
       ├─ YES → Continue
       └─ NO  → Delegate to iMSCP
       ↓
Check: themes/default/templates/admin/index.phtml exists?
       ├─ YES → Continue
       └─ NO  → Delegate to iMSCP
       ↓
Load Layout XML file
       ↓
Parse: Find all blocks defined
       ↓
For each block:
       ├─ Load: PHP Block class
       ├─ Load: Data from indexData.xml
       ├─ Inject: Data into block
       └─ Render: Block output
       ↓
Load: Template file (index.phtml)
       ↓
Render: Template with block data
       ↓
Load: CSS files (theme.css, dark-mode.css)
       ↓
Load: JavaScript files (app.js)
       ↓
Return: Complete HTML page
       ↓
Browser renders and displays
```

---

## 📦 What Gets Created When?

```
PHASE 0 (FIX ERROR)
  [x] Debug information
  [x] Error logs reviewed
  
PHASE 1 (ARCHITECTURE)
  [+] Folder documentation
  [+] Import verification
  [+] src/Debug/Debugger.php ✨

PHASE 2 (LAYOUT/BLOCK)
  [+] themes/default/layouts/admin.xml ✨
  [+] themes/default/layouts/admin_index.xml ✨
  [+] themes/default/templates/page/layout.phtml ✨
  [+] themes/default/templates/page/header.phtml ✨
  [+] themes/default/templates/page/footer.phtml ✨

PHASE 3 (DATA)
  [+] src/DataLoader.php ✨
  [+] src/VariableResolver.php ✨

PHASE 4 (ROUTING)
  [✎] src/Controller/ImscpPageController.php (modify)

PHASE 5 (ADMIN PAGE) ⭐ FIRST COMPLETE PAGE
  [+] src/Block/Admin/IndexBlock.php ✨
  [+] src/Block/Admin/UsersSection.php ✨
  [+] src/Block/Admin/ResellersSection.php ✨
  [+] src/Block/Admin/StatisticsBlock.php ✨
  [+] themes/default/templates/admin/index.phtml ✨
  [+] themes/default/data/admin/indexData.xml ✨

PHASE 6 (STYLING)
  [+] themes/default/assets/css/theme.css ✨
  [+] themes/default/assets/css/dark-mode.css ✨
  [+] themes/default/assets/js/app.js ✨
  [+] themes/default/assets/js/dark-mode.js ✨

PHASE 7 (HOOKS)
  [✎] DataBuilderIMSCPBetaPlugin.php (modify - add hooks)

PHASE 8 (DOCS)
  [+] docs/DEVELOPER_GUIDE.md ✨
  [+] docs/API_REFERENCE.md ✨
  [+] docs/ARCHITECTURE.md ✨

PHASE 9 (TESTING)
  [+] tests/unit/CoreEngineTest.php ✨
  [+] tests/integration/PageRenderTest.php ✨

TOTAL:
  35 files created
  6 files modified
```

---

## ⏱️ TIME ALLOCATION

```
Phase 0 (Fix error)         ████ 4 hours
Phase 1 (Architecture)      ██████ 5 hours
Phase 2 (Layout/Block)      ███████████ 7 hours
Phase 3 (Data)              ██████ 5 hours
Phase 4 (Routing)           ███████ 5 hours
Phase 5 (Admin page)        ███████████ 7 hours        ⭐ FIRST WORKING PAGE
Phase 6 (Styling)           ████████████ 9 hours
Phase 7 (Hooks)             ████ 4 hours
Phase 8 (Documentation)     ████████ 6 hours
Phase 9 (Testing)           ███████ 7 hours
Phase 10 (Optimization)     █████ 5 hours

Total: 54-68 hours (~2 weeks at 5h/day)
```

---

## 🎯 KEY FILES YOU'LL CREATE

### MUST CREATE (Critical Path)
```
✨ src/Block/Admin/IndexBlock.php        ← Logic for admin page
✨ themes/default/layouts/admin_index.xml ← Page structure
✨ themes/default/templates/admin/index.phtml ← Page HTML
✨ themes/default/data/admin/indexData.xml ← Page data
```

### SHOULD CREATE (Core System)
```
✨ themes/default/assets/css/theme.css   ← Styling
✨ src/DataLoader.php                    ← Load data files
✨ src/VariableResolver.php              ← Replace variables
```

### NICE TO CREATE (Enhancement)
```
✨ docs/DEVELOPER_GUIDE.md               ← For developers
✨ tests/integration/PageRenderTest.php  ← Test suite
✨ src/Debug/Debugger.php                ← Debug tools
```

---

## 🚦 STATUS INDICATORS

```
🔴 RED    = Blocked / Critical Issue
🟡 YELLOW = In Progress / Partial
🟢 GREEN  = Complete / Working
⚪ GRAY   = Not Started

Current Status:
═════════════════════════════════════════

Phase 0: 🔴 CRITICAL - Fix dark mode error first
Phase 1: 🟡 40% done - Architecture exists but needs docs
Phase 2: 🟡 30% done - Some files exist, many to create
Phase 3: 🟡 40% done - System designed, not implemented
Phase 4: 🟡 20% done - Controller exists, improve routing
Phase 5: 🟡 50% done - Some pieces done, not complete
Phase 6: 🟡 10% done - No CSS yet, needs creation
Phase 7: 🟡 30% done - Partial hooks, needs completion
Phase 8: ⚪ 0% done - No docs written yet
Phase 9: ⚪ 0% done - No tests written yet
Phase 10: ⚪ 0% done - Not started

Overall: 25% Complete (17 of 68 hours done)
```

---

## 📈 SUCCESS MILESTONES

```
MILESTONE 1: Phase 0 Complete ✓
  └─ /admin/databuilder loads without error
  └─ ETA: 1-2 days

MILESTONE 2: Phase 5 Complete ✓
  └─ Admin index page fully working
  └─ All sections render
  └─ ETA: 3-5 days

MILESTONE 3: Phase 6 Complete ✓
  └─ Page looks good with CSS
  └─ Dark mode works
  └─ ETA: 5-7 days

MILESTONE 4: Phase 7 Complete ✓
  └─ Plugin installs/uninstalls cleanly
  └─ ETA: 7-8 days

MILESTONE 5: All Phases Complete ✓
  └─ Full system working
  └─ Documented and tested
  └─ ETA: 10-14 days
```

---

## 🎓 THREE PART PATTERN

Every page in DataBuilder follows this pattern:

```
┌──────────────────────────────────────────────────────┐
│              ADMIN INDEX PAGE EXAMPLE                │
├──────────────────────────────────────────────────────┤
│                                                      │
│ 1. LAYOUT (XML)                                      │
│    ↓                                                 │
│    themes/default/layouts/admin_index.xml           │
│    └─ Defines: Which blocks to show, where          │
│       └─ Block name: "admin.index"                  │
│       └─ Template: "admin/index.phtml"              │
│       └─ Children: "users", "resellers"             │
│                                                      │
│ 2. BLOCK (PHP)                                       │
│    ↓                                                 │
│    src/Block/Admin/IndexBlock.php                   │
│    └─ Defines: What data to load, how to process   │
│       └─ Load: adminData.xml                        │
│       └─ Load: User count from iMSCP                │
│       └─ Format: Data array                         │
│                                                      │
│ 3. TEMPLATE (HTML/PHTML)                            │
│    ↓                                                 │
│    themes/default/templates/admin/index.phtml      │
│    └─ Defines: How to render HTML                   │
│       └─ Variables: From block or data              │
│       └─ Loops: For lists, tables, etc              │
│       └─ Conditions: Show/hide sections             │
│                                                      │
│ 4. DATA (XML) - OPTIONAL                            │
│    ↓                                                 │
│    themes/default/data/admin/indexData.xml         │
│    └─ Stores: Default values, structure             │
│       └─ Variables: {ADMIN_USERS}, etc              │
│       └─ Merged: With iMSCP data at runtime         │
│                                                      │
└──────────────────────────────────────────────────────┘

RESULT: Complete working page!
```

---

## 🎯 YOUR JOURNEY

```
START (TODAY)
    ↓
Read QUICKSTART.md (10 min)
    ↓
Fix Phase 0 Error (3-4 hours)
    ↓
Create Admin Index Page (7 hours)
    ↓
Add CSS Styling (8 hours)
    ↓
Complete Core Systems (15 hours)
    ↓
Installation & Hooks (4 hours)
    ↓
Documentation (6 hours)
    ↓
Testing (7 hours)
    ↓
Polish & Optimize (5 hours)
    ↓
COMPLETE ✅ (54-68 hours total)
```

---

## 💡 REMEMBER

```
✅ DataBuilder = Template/View Engine ONLY
✅ No Database Queries
✅ All Data from iMSCP
✅ Fallback to iMSCP if page not in DataBuilder
✅ Users can override without modifying source
✅ Three-part pattern: Layout + Block + Template

❌ Never modify /imscp folder
❌ Never query database directly
❌ Never hardcode data
❌ Never skip phases
✅ Always test after changes
✅ Always check error logs
✅ Always enable debug mode when stuck
```

---

**Next Step:** Open **QUICKSTART.md** RIGHT NOW and follow Phase 0 instructions!

Good luck! 🚀
