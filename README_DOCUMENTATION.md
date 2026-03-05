# 📚 DataBuilder iMSCP Plugin - DOCUMENTATION INDEX

**Version:** 1.0.0-beta  
**Created:** March 5, 2026  
**Status:** Implementation Ready

---

## 🎯 START HERE

You've received **4 comprehensive documents** to guide the DataBuilder iMSCP plugin development. Start in this order:

### 1. 🚀 **QUICKSTART.md** (READ THIS FIRST - 10 min)
**Purpose:** Get up to speed immediately  
**Contains:**
- Project status (what's done, what's broken, what's missing)
- How to fix the urgent Phase 0 error
- Step-by-step checklist to start TODAY
- Common issues & solutions
- Sample code snippets

**When to use:** First thing when you start work

**Next:** IMPLEMENTATION_PLAN.md

---

### 2. 📋 **IMPLEMENTATION_PLAN.md** (READ SECOND - 20 min)
**Purpose:** Understand the complete strategy  
**Contains:**
- 10 phases of development (0-10)
- Detailed task breakdown for each phase
- Architecture diagrams and concepts
- Files to create and modify
- Priority matrix (P0, P1, P2, P3)
- Success criteria and validation checklist

**When to use:** Planning your sprint, understanding big picture

**Next:** STRUCTURED_TODO.md

---

### 3. ✅ **STRUCTURED_TODO.md** (READ THIRD - 15 min)
**Purpose:** Detailed task tree with checkboxes  
**Contains:**
- Phase-by-phase breakdown
- Hierarchical task trees
- File-by-file checklist
- Status tracking for each task
- Estimated hours per phase
- Dependencies between phases

**When to use:** Daily work - track which task to do next

**Next:** TODO_TRACKER.json

---

### 4. 📊 **TODO_TRACKER.json** (REFERENCE)
**Purpose:** Programmatic tracking and automation  
**Contains:**
- Machine-readable project structure
- Phase status and progress
- Task dependencies
- File creation/modification lists
- Success metrics

**When to use:** If building tracking tools or automation scripts

---

## 📂 DOCUMENT HIERARCHY

```
📚 DOCUMENTATION INDEX (You are here)
│
├── 🚀 QUICKSTART.md
│   └── Quick overview + urgent fixes + daily checklist
│
├── 📋 IMPLEMENTATION_PLAN.md
│   └── Complete strategy + 10 phases detailed + priorities
│
├── ✅ STRUCTURED_TODO.md
│   └── Task breakdown + file checklist + tracking
│
└── 📊 TODO_TRACKER.json
    └── Machine-readable project data
```

---

## 🔴 CRITICAL PATH (Start Here!)

```
Phase 0: FIX ERROR (2-4 hours)
    ↓
Phase 5: ADMIN INDEX PAGE (7-8 hours)
    ↓
Phase 6: STYLING (8-10 hours)
    ↓
Phase 2,3,4: SYSTEMS (Parallel with phase 5-6)
    ↓
Phase 7: HOOKS (3-4 hours)
    ↓
Phase 8,9: DOCS & TESTS (4-7 hours)
    ↓
Phase 10: OPTIMIZATION (4-5 hours)
```

**Total estimate:** 50-68 hours

---

## 📑 REFERENCE TABLE

| Document | Best For | Read Time | Priority |
|----------|----------|-----------|----------|
| QUICKSTART.md | Getting started, daily work | 10 min | ⭐⭐⭐⭐⭐ |
| IMPLEMENTATION_PLAN.md | Understanding strategy, planning | 20 min | ⭐⭐⭐⭐ |
| STRUCTURED_TODO.md | Daily checklist, task tracking | 15 min | ⭐⭐⭐⭐ |
| TODO_TRACKER.json | Automation, programmatic use | 5 min | ⭐⭐⭐ |

---

## 🎯 YOUR FIRST DAY

### Morning (Preparation - 30 min)
1. **Read:** QUICKSTART.md (10 min)
2. **Understand:** Current project status
3. **Identify:** The error in Phase 0
4. **Prepare:** Development environment

### Afternoon (Implementation - 2-3 hours)
1. **Fix:** Phase 0 error (critical bug)
2. **Create:** Admin index layout (Phase 5.1.1)
3. **Create:** Admin index block (Phase 5.3.1)
4. **Test:** Both work together
5. **Document:** What you did

### Evening (Planning - 30 min)
1. **Read:** IMPLEMENTATION_PLAN.md for context
2. **Review:** STRUCTURED_TODO.md
3. **Plan:** Next day's tasks
4. **Check:** Documentation for Phase 1

---

## 🗺️ NAVIGATION GUIDE

### "I want to..."

**...understand what the project is about**
→ QUICKSTART.md → "Current Project Status"

**...know what to do first**
→ QUICKSTART.md → "Urgent: Phase 0"

**...see the big picture**
→ IMPLEMENTATION_PLAN.md → "Architecture"

**...know exactly what files to create**
→ STRUCTURED_TODO.md → "Summary by File" OR TODO_TRACKER.json → "files_to_create"

**...check if I'm on schedule**
→ TODO_TRACKER.json → "metrics.estimated_completion_date"

**...understand the dark mode error**
→ QUICKSTART.md → "Urgent: Phase 0"

**...see how long Phase X takes**
→ STRUCTURED_TODO.md → Phase heading OR TODO_TRACKER.json → "phases"

**...know what blocks what**
→ TODO_TRACKER.json → "dependencies" OR STRUCTURED_TODO.md → "Task Tree"

**...get sample code**
→ QUICKSTART.md → "Sample Code Snippets"

**...report progress**
→ TODO_TRACKER.json → Update status fields

---

## 📊 PROJECT STATUS OVERVIEW

### By the Numbers
- **Total Phases:** 10
- **Total Tasks:** ~60
- **Files to Create:** 35+
- **Files to Modify:** 6
- **Estimated Hours:** 50-68
- **Target Completion:** April 5, 2026

### Current Progress
- **Complete:** 25%
- **In Progress:** ~50%
- **Not Started:** ~25%

### Critical Blockers
1. ❌ Phase 0: Dark mode error (MUST FIX FIRST)
2. ✅ Phase 1: Architecture (Mostly done)
3. 🟡 Phase 5: Admin page (In progress)

---

## 🔑 KEY PRINCIPLES (Remember These!)

1. **DataBuilder = Rendering Engine ONLY**
   - No database queries
   - No business logic
   - Pure view/template layer

2. **Three Components = One Page**
   - **Layout** (XML) - Structure
   - **Block** (PHP) - Logic
   - **Template** (PHTML) - Output

3. **Three Theme Layers**
   - **base/** - Core (don't modify)
   - **default/** - Current theme
   - **custom/** - User overrides

4. **Zero Modification to iMSCP**
   - Never touch `/imscp` folder
   - Plugin works as theme
   - Fallback to iMSCP if needed

5. **Data Separation**
   - Data files separate from templates
   - XML/JSON for data definition
   - Templates reference data variables

---

## 📚 RELATED RESOURCES

### In This Folder
- `README.md` - Plugin overview
- `TODO_IMPLEMENTATION.md` - Technical implementation details
- `TODO_ADMIN_INDEX.md` - Admin index page notes
- `NOTES.md` - (Currently empty, for your notes)

### In iMSCP Folder
- `imscp/gui/themes/default/` - Reference theme structure
- `imscp/gui/themes/i-mscp/` - Another reference theme
- `imscp/gui/src/` - iMSCP code (for understanding integration)

---

## 💡 TIPS FOR SUCCESS

### Documentation Tips
1. **Keep QUICKSTART.md open** while coding
2. **Reference code snippets** when creating files
3. **Check STRUCTURED_TODO.md** for file paths
4. **Review architecture** in IMPLEMENTATION_PLAN.md when confused

### Code Organization Tips
1. **One file = one class/block**
2. **File path = namespace** (Block/Admin → src/Block/Admin)
3. **Template paths** match block structure
4. **Data files** named after templates

### Testing Tips
1. **Test after each phase**
2. **Check browser console** for JS errors
3. **Enable debug mode** when troubleshooting
4. **Read error logs** first when stuck

### Time Management
1. **Phase 0**: ~3-4 hours (URGENT)
2. **Phase 5**: ~7-8 hours (demonstrates working page)
3. **Phases 2-4**: ~15 hours (systems)
4. **Phase 6**: ~8-10 hours (styling)
5. **Phase 7**: ~3-4 hours (hooks)
6. **Phases 8-10**: ~10-15 hours (docs, tests, polish)

---

## ❓ FAQ

**Q: Where do I start?**  
A: QUICKSTART.md → Fix Phase 0 error

**Q: How long will this take?**  
A: ~50-68 hours total, or ~2 weeks at 5 hours/day

**Q: Can I skip phases?**  
A: No, phases have dependencies. Follow order in IMPLEMENTATION_PLAN.md

**Q: What if I get stuck?**  
A: Check QUICKSTART.md "If You Get Stuck" section

**Q: How do I know if I'm on track?**  
A: Check status in TODO_TRACKER.json

**Q: Can I modify iMSCP files?**  
A: NO - Only modify files in databuilder/ plugin folder

**Q: What if something breaks?**  
A: Check error logs, enable debug mode, add logging

**Q: How do I report progress?**  
A: Update TODO_TRACKER.json and STRUCTURED_TODO.md

---

## 🚀 QUICK START ACTION ITEMS

### FOR IMMEDIATE ACTION (Next 30 minutes)

- [ ] Read QUICKSTART.md completely
- [ ] Understand the dark mode error (Phase 0)
- [ ] Check your error logs
- [ ] Enable debug mode in config.php
- [ ] Try accessing `/admin/databuilder`

### FOR TODAY (Next 2-4 hours)

- [ ] Fix Phase 0 error or document findings
- [ ] Once error is fixed, create admin_index.xml (Phase 5.1.1)
- [ ] Create IndexBlock.php (Phase 5.3.1)
- [ ] Create index.phtml template (Phase 5.2.1)
- [ ] Test that it works

### FOR THIS WEEK

- [ ] Complete Phase 5 (admin index page)
- [ ] Complete Phase 6 (styling + dark mode)
- [ ] Start Phase 2 (layout system)
- [ ] Start Phase 3 (data system)

---

## 📞 SUPPORT

### If You Need Help
1. **Check logs first:** `tail -f /var/log/apache2/error.log`
2. **Enable debug:** Set `'debug' => true` in config.php
3. **Consult documentation:** Check QUICKSTART.md section "If You Get Stuck"
4. **Use sample code:** Copy from QUICKSTART.md snippets
5. **Review error output:** Check browser console (F12)

### Documentation Review
- **For understanding concept:** IMPLEMENTATION_PLAN.md → Architecture
- **For seeing tasks:** STRUCTURED_TODO.md → Phase X
- **For code samples:** QUICKSTART.md → Sample Code
- **For file paths:** TODO_TRACKER.json → files_to_create

---

## 📈 PROGRESS TRACKING

### Update These Files As You Work

1. **STRUCTURED_TODO.md**
   - Mark tasks as "IN_PROGRESS" when you start
   - Mark tasks as "DONE" when complete
   - Update progress percentages

2. **TODO_TRACKER.json**
   - Update "status" field for phases
   - Update "progress" percentage
   - Track "overall_progress"

3. **QUICKSTART.md**
   - Add notes in "Notes for You" section
   - Track issues encountered
   - Document solutions found

---

## ✨ YOU'RE READY TO START!

Everything you need is in these 4 documents. Follow them in order:

1. **QUICKSTART.md** - Get running
2. **IMPLEMENTATION_PLAN.md** - Understand strategy
3. **STRUCTURED_TODO.md** - Daily checklist
4. **TODO_TRACKER.json** - Progress tracking

**Begin with QUICKSTART.md NOW → Phase 0!**

Good luck! 🚀

---

**Questions?** Check the relevant section in the documentation above.
