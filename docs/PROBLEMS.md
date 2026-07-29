# TravianZ - Problems & Issues Analysis

## 🔴 Critical Security Issues

### 1. SQL Injection Vulnerabilities
The codebase has a **dual query system**: old `$database->query()` (raw mysqli_query) and new `$database->query_new()` (prepared statements with parameter binding).

**Problem**: Many parts of the code still use the old unsafe `query()` method with string interpolation:
```php
// UNSAFE - string interpolation without escaping
$q = "Insert into " . TB_PREFIX . "illegal_log SET user = $uid, log = '$log'";
$database->query($q);
```

While `escape_input()` is sometimes called, string interpolation is inherently risky. The migration to `query_new()` with prepared statements is incomplete.

### 2. XSS (Cross-Site Scripting)
- Templates use raw PHP `echo` without consistent escaping
- Only the `RemoveXSS()` method exists (in DB class), but it's HTML-escaping, not context-aware
- Many user inputs rendered directly in templates
- Username validation was recently tightened (issue #184), but other fields may still be vulnerable

### 3. CSRF Protection Gap
- Admin panel has CSRF protection (`Admin/csrf.php`)
- **Frontend game actions lack CSRF tokens** — the main game forms use `$_POST['ft']` action codes without CSRF validation
- An attacker could forge game actions (build, attack, trade, etc.)

### 4. Hardcoded Backdoor
```php
// In Account.php
if (strtolower($_POST['name']) === 'shadow') {
    $database->updateUserField($uid, 'access', ADMIN, 1);
}
```
Any user registering with username "shadow" (case-insensitive) gets **admin access**. This is a well-known backdoor.

### 5. Session Security
- Cookies set without `secure` flag (only `httponly=true`):
```php
setcookie("COOKUSR", rawurlencode($_POST['name']), time()+COOKIE_EXPIRE, COOKIE_PATH, '', false, true);
```
- No session regeneration on login
- Session stored in PHP default (files), no database-backed sessions

---

## 🟠 Major Architectural Problems

### 6. Global Variable Reliance
The entire codebase relies on PHP global variables:
```php
global $database, $session, $form, $generator, $logging, $market, $building, $technology;
```
This makes:
- Testing nearly impossible
- Dependencies invisible
- Refactoring extremely risky
- Side effects unpredictable

### 7. Inconsistent Coding Paradigms
The codebase mixes multiple coding styles:
- **Procedural**: Global functions, direct DB calls in templates
- **OOP (old style)**: Classes without constructors, public properties
- **OOP (modern)**: Namespaced classes with interfaces and dependency injection
- **Trait-based**: Phase S1/S2 refactoring

This creates confusion and makes the codebase harder to maintain.

### 8. Monolithic Classes
Even post-refactoring:
- `MYSQLi_DB` still composes 14 traits (~200+ methods)
- `Automation` composes 13 traits (~100+ methods)
- No separation of concerns within the DB layer (query building, caching, connection management all mixed)

### 9. No Front Controller / Router
Each page is its own PHP file at the project root:
- `dorf1.php`, `dorf2.php`, `build.php`, `karte.php`, `berichte.php`, etc.
- No centralized routing
- Each file duplicates bootstrapping code (loading autoloader, config, DB)
- URL structure is fixed and not SEO-friendly

### 10. Mixed Romanian + English Comments
```php
// ==================== VERIFICARE ERORI ==================== (Romanian)
// ==================== PROCESARE ÎNREGISTRARE ==================== (Romanian)
// Vacation mode by Shadow (English)
```
Comments are in both Romanian and English, sometimes in the same file. This creates a barrier for international contributors.

---

## 🟡 Code Quality Issues

### 11. Copy-Paste Autoloader Bootstrap
The same autoloader discovery pattern is repeated in virtually every GameEngine file:
```php
global $autoprefix;
$autoprefix = '';
for ($i = 0; $i < 5; $i++) {
    $autoprefix = str_repeat('../', $i);
    if (file_exists($autoprefix.'autoloader.php')) {
        include_once $autoprefix.'autoloader.php';
        break;
    }
}
```
This should be handled once, in the entry point.

### 12. Typo-Ridden Constants
Variable names and constants contain spelling errors that persist in the codebase:
- `$bid18` → should be `$bid18`
- `$conqured` → should be `$conquered`
- `$trappercap` → should be `$trappercap`
- `evasionspeed` → should be `evasionspeed`
- `maintenenceResetPlus.tpl` → `maintenance`
- `autoprefix` → should be `autoprefix`
- `recieve` → should be `receive`
- `avaliable` → should be `available`
- `alliane` → should be `alliance`

These typos are in:
- Database column names (`conqured`)
- File names
- Class/function names
- Template file names
- Configuration constant names

### 13. No Error Handling Consistency
- Some files use `die()` with HTML
- Some use exceptions
- Some silently suppress errors with `@`
- No centralized error handler
- `error_reporting(E_ALL || E_NOTICE)` — this is a bug (`||` should be `|`), it evaluates to `E_ALL || 8 = true = 1`

```php
error_reporting(E_ALL || E_NOTICE); // This equals error_reporting(1)!
```

### 14. No Type Declarations
PHP 7+ type declarations are almost entirely absent from:
- Function parameters
- Return types
- Class properties

Only the newer `src/` code and recently refactored areas use them.

### 15. Database Query Counter Bug
```php
[isInt check for float with int cast issue in query_new dead code path]
```
The prepared statement path in `query_new()` has complex conditional logic that's hard to follow and may have edge cases.

---

## 🟡 Database Problems

### 16. No Foreign Keys
The schema defines no foreign key constraints. Referential integrity is entirely managed by application code, meaning:
- Orphan records are possible (and common)
- No cascade deletes/updates
- Data integrity bugs are hard to catch

### 17. No Migrations System
Database schema changes are made by:
- Manually editing `struct.sql`
- Running ALTER TABLE statements ad-hoc
- No version tracking of schema changes
- No rollback capability

### 18. Inefficient Column Design
- `enforcement` table has **90 unit columns** (u1-u90) — should be normalized
- `fdata` table has **80 columns** (f1-f40 with f1t-f40t) — key-value pattern would be cleaner
- `tdata` (tech data) has sparse columns (gaps in numbering: t2-t9, t12-t19, t22-t29, t32-t39, t42-t46)

### 19. Missing Indexes
Some tables lack indexes on frequently filtered/sorted columns, potentially causing full table scans on large datasets.

### 20. `utf8` Charset (Not `utf8mb4`)
Tables use `CHARSET=utf8` (MySQL's 3-byte UTF-8), which does **not** support emoji or some Unicode characters. Should be `utf8mb4`.

---

## 🟡 Frontend / UX Problems

### 21. Legacy JavaScript Framework
**MooTools 1.x** (released ~2008, unmaintained since 2015):
- No ES6+ support
- No module system
- Incompatible with modern tooling
- Security vulnerabilities in old version
- Files are concatenated into large monolithic scripts (~125KB minified core)

### 22. No Mobile Responsiveness
- Fixed-width layout (designed for 1024px+ screens)
- No responsive CSS or media queries
- Not playable on mobile devices

### 23. XHTML Doctype
```html
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
```
- XHTML 1.0 is obsolete
- Self-closing tags (`<img ... />`) in HTML5 context
- No HTML5 semantic elements

### 24. Inline Styles & Scripts
- CSS embedded in `<style>` tags in page head
- JavaScript embedded in `<script>` tags at page bottom
- No external stylesheet for page-specific styles
- `<!-- li.c4 { ... } -->` — HTML comments inside `<style>` (XHTML legacy)

---

## 🟢 Minor / Cosmetic Issues

### 25. `.htaccess` Files Everywhere
Security through directory listing prevention:
```
img/index.php
img/rpage/index.php
Templates/Build/index.php
...
```
Hundreds of empty `index.php` files and `.htaccess` files as directory listing guards. A single server config or proper routing would eliminate this.

### 26. Duplicate Functionality
- `karte.php` AND `karte2.php` — two map implementations
- `dorf1.php`, `dorf2.php`, `dorf3.php` — three village views
- `plus.php` AND `plus1.php` — two Plus pages
- `a2b.php` AND `a2b2.php` — two troop movement interfaces

### 27. Configuration File Location
`GameEngine/config.php` is a generated file containing database credentials, game settings, and PayPal API keys. It's in a web-accessible location (though PHP files won't render). Best practice would put it outside the web root.

### 28. `index.php` Redirect Files
Many `index.php` files exist solely to prevent directory listing:
- They contain only `<?php` or a redirect
- This is a security anti-pattern (defense in depth is good, but this is noise)

### 29. Old PHP Extensions
```php
if(!file_exists('var/installed') && @opendir('install'))
```
Using `@opendir` to check if a directory exists (should use `is_dir()`).

### 30. Romanian Text in Error Messages
Some error messages and debug output are in Romanian:
```php
$log .= "Start Construction of ";
```
Mixed language debug output makes log analysis harder for non-Romanian server operators.

### 31. No Unit Tests
The entire codebase has **zero automated tests**:
- No PHPUnit configuration
- No test directory
- No CI/CD pipeline
- All testing is manual

### 32. No Composer / Dependency Management
- No `composer.json`
- No third-party package management
- All libraries are vendored manually
- Makes dependency updates and security patching impossible

### 33. Cron Security
`cron.php` is called via HTTP with a key:
```php
$findReplace["%CRONKEY%"] = bin2hex(random_bytes(24));
```
This is better than nothing, but a CLI-based cron job would be more secure and efficient.

### 34. Template File Explosion
The `Templates/Build/` directory has **~95 template files** for individual building views. A single dynamic template with configuration-driven rendering would be more maintainable.

### 35. Query Counter Not Thread-Safe
Query counters (`$selectQueryCount`, etc.) are stored in the DB object and reset per request. Under high load with persistent connections, this could be inaccurate.

---

## Summary: Risk Matrix

| Severity | Count | Key Areas |
|----------|-------|-----------|
| 🔴 Critical | 5 | SQL injection, XSS, CSRF, hardcoded backdoor, session security |
| 🟠 Major | 5 | Global variables, mixed paradigms, monolithic classes, no routing, mixed languages |
| 🟡 Moderate | 10 | Code quality, database design, frontend tech debt |
| 🟢 Minor | 15 | File organization, missing tooling, cosmetic |

---

## Recommended Priority Actions

1. **Remove the hardcoded admin backdoor** (immediate)
2. **Complete migration to prepared statements** (query_new) for all user-input queries
3. **Add CSRF protection to all game forms**
4. **Fix `error_reporting(E_ALL || E_NOTICE)`** bug
5. **Move config.php outside web root**
6. **Add HTTPS enforcement and secure cookie flags**
7. **Introduce a proper front controller (index.php routing)**
8. **Begin migration from MooTools to vanilla JS or modern framework**
9. **Add PHPUnit and start with critical path tests**
10. **Introduce Composer for dependency management**
