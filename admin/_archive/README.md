# Archive Folder - README

**Purpose:** This folder contains old test files, debug utilities, and one-time setup scripts that are no longer needed for production.

**Last Updated:** March 1, 2026

---

## 📂 Contents

### Root Level Files (Moved March 1, 2026)

| File | Purpose | Type | Notes |
|------|---------|------|-------|
| `test-news-db.php` | Database connection test for news module | Test Utility | Development only - not needed in production |
| `seed-dummy-news.php` | Add sample/demo news articles | Demo/Test | Development/testing - generates fake content |
| `clear-dummy-news.php` | Remove demo news articles | Demo/Test | Development/testing - cleans up fake content |
| `enable-team-module.php` | Setup utility to enable team module | Setup Utility | One-time setup script - functionality moved to admin-settings.php |
| `team-setup.php` | Team module setup and verification | Setup Utility | Development/setup only - functionality covered elsewhere |
| `update-admin-name.php` | Admin name update utility | Utility | Empty/unused file |

**Reason for Moving:** These are testing, demo, and setup utilities not needed in production. The demo buttons in news-list.php have been commented out.

---

### `/test-and-debug/` - Test & Debug Files
These files were used during development and debugging phases but are not needed for production operation.

| File | Purpose | Status |
|------|---------|--------|
| check-columns.php | Check database column structure | ❌ Not needed |
| check-table.php | Verify table schema | ❌ Not needed |
| debug-forms.php | Debug form submissions | ❌ Not needed |
| test-check-forms.php | Verify form data | ❌ Not needed |
| test-form-insert.php | Test form insertion | ❌ Not needed |
| test-modal-debug.html | Debug modal functionality | ❌ Not needed |
| test-submit.php | Test form submission | ❌ Not needed |
| test-tooltip.html | Test tooltip display | ❌ Not needed |
| test-tooltip.php | Tooltip functionality test | ❌ Not needed |
| verify-sports-fix.sh | Verify sports module fixes | ❌ Not needed |
| TESTING_GUIDE.html | Testing documentation | ❌ Outdated |

### `/setup/` - Setup & Migration Files
One-time setup and data migration scripts. Only needed if recreating database or migrating data.

| File | Purpose | Status |
|------|---------|--------|
| setup.php | Create database tables | ⚠️ Keep for reference |
| migrate-financing-forms.php | Migrate financing form data | ⚠️ Keep for reference |

---

## 🔄 When to Use Archive Files

### You May Need These If:
- ✓ Recreating the database from scratch → Use `setup/setup.php`
- ✓ Need to verify database schema → Use `test-and-debug/check-table.php`
- ✓ Debugging a specific module → Use corresponding test files
- ✓ Migrating data from old format → Use `setup/migrate-financing-forms.php`
- ✓ Generating test/sample news data → Use `seed-dummy-news.php` and `clear-dummy-news.php`

### You Don't Need These If:
- ✓ Running production admin panel → Skip these files
- ✓ Testing normal form submissions → Use actual forms
- ✓ Debugging existing functionality → Use browser dev tools
- ✓ Want clean, minimal directory → Keep archive closed

---

## ✅ Files Still in Main Admin Folder (ACTIVE)

### Essential Admin Pages
- `index.php` - Dashboard
- `login.php`, `logout.php` - Authentication
- `profile.php`, `change-password.php` - User management

### Content Management
- `news-list.php`, `news-add.php`, `news-edit.php` - News articles
- `team.php` - Team members management
- `admin-settings.php` - Global settings

### Form Management
- `forms-financing.php` - Financing form submissions
- `forms-sports.php` - Sports investment form submissions
- `forms-mosaic.php` - Mosaic software form submissions
- `forms-prospera.php` - Prospera form submissions
- `forms-contact.php` - Contact form submissions
- `submit-form.php` - Form submission handler
- `forms-view-modal*.php` - Modal views for forms

### APIs & Utilities
- `api-*.php` - API endpoints (team members, modal actions, spam toggle, etc.)
- `get-*.php` - Data retrieval APIs (published news, form data, sports categories)
- `logs.php` - Activity logging

### Core Infrastructure
- `/config/` - Database and authentication configuration
- `/includes/` - Header, footer, helpers
- `/assets/` - CSS, JS, images
- `/js/` - JavaScript modules (form-modal.js, action-handler.js)

---

## 🚨 Important Notes

1. **Database Structure** - If you need the database schema, refer to `setup/setup.php` but don't run it on production.

2. **Debugging** - Modern debugging should use:
   - Browser Developer Tools (F12)
   - PHP error logs
   - Database query logs
   - Helper functions in `/includes/helpers.php`

3. **Testing** - For testing form pages:
   - Visit the form pages directly
   - Use the actual submission forms
   - Test through the admin interface

4. **No Deletion Needed** - Archive files take minimal space, so no need to delete them.

---

## 📚 Reference Documentation

For the current admin panel, refer to these files in the main `/admin/` directory:

- **_ADMIN_PANEL_REVIEW.md** - Detailed audit of all issues
- **REFACTORING_PLAN.md** - Implementation roadmap
- **AUDIT_SUMMARY.md** - Executive summary
- **TECHNICAL_REFERENCE.md** - Developer guide with helper functions

---

## ✅ Best Practices

### When Adding New Debug Files:
1. Create them in a `_debug/` subfolder (not in main directory)
2. Add date prefix: `2026-02-27-test-feature.php`
3. Document purpose clearly at top of file
4. Move to archive when done debugging
5. Update this README

### When Using Archive Files:
1. Always make a backup first
2. Test in development environment
3. Never run production queries on live database
4. Document what you're testing
5. Clean up temporary test data

---

**Archive Created:** February 27, 2026  
**Last Updated:** February 27, 2026  
**Status:** ✅ Complete - No maintenance needed

