# IPCR System v8 — Full Codebase Audit & Recommendations

> **Generated:** March 3, 2026
> **Scope:** Complete analysis of models, controllers, middleware, services, migrations, seeders, views, frontend assets, and routing.
> **Purpose:** Actionable bug fixes, security patches, and architectural improvements for another AI agent to implement.

---

## Table of Contents

1. [Critical Bugs (Fix Immediately)](#1-critical-bugs-fix-immediately)
2. [Security Vulnerabilities](#2-security-vulnerabilities)
3. [Architectural Redundancies](#3-architectural-redundancies)
4. [Model Layer Issues](#4-model-layer-issues)
5. [Controller Layer Issues](#5-controller-layer-issues)
6. [Middleware Issues](#6-middleware-issues)
7. [Service Layer Issues](#7-service-layer-issues)
8. [Migration & Seeder Issues](#8-migration--seeder-issues)
9. [Frontend & View Issues](#9-frontend--view-issues)
10. [Route Issues](#10-route-issues)
11. [Notification Issues](#11-notification-issues)
12. [Missing Features](#12-missing-features)
13. [Recommended Implementation Order](#13-recommended-implementation-order)

---

## 1. Critical Bugs (Fix Immediately)

### 1.1 `RedirectIfAuthenticated` calls non-existent method
- **File:** `app/Http/Middleware/RedirectIfAuthenticated.php` (line ~27)
- **Bug:** `$user->guard('web')->hasRole('admin')` — `guard()` is not a method on the `User` model. This throws a `BadMethodCallException` at runtime for ANY authenticated user hitting a guest-middlewared route (e.g., visiting `/login` while logged in).
- **Fix:** Change to `$user->hasRole('admin')`.

### 1.2 `PhotoService` uses `env()` instead of `config()`
- **File:** `app/Services/PhotoService.php` (lines 16-18)
- **Bug:** Uses `env('CLOUDINARY_CLOUD_NAME')`, `env('CLOUDINARY_API_KEY')`, `env('CLOUDINARY_API_SECRET')` directly. When config is cached via `php artisan config:cache` (standard in production), `env()` returns `null` for all values. **Photo uploads break completely in production.**
- **Fix:** Replace with `config('cloudinary.cloud_name')`, `config('cloudinary.api_key')`, `config('cloudinary.api_secret')` — or use the existing `config/cloudinary.php` config file.

### 1.3 `PasswordResetNotification` references non-existent User attributes
- **File:** `app/Notifications/PasswordResetNotification.php` (line ~41)
- **Bug:** `$notifiable->first_name` and `$notifiable->last_name` — The `User` model only has a single `name` column. These attributes are always `null`, rendering the greeting as `"Hello  !"` (empty).
- **Fix:** Change to `$notifiable->name`.

### 1.4 `IpcrTemplate` and `OpcrTemplate` missing `is_active` boolean cast
- **Files:** `app/Models/IpcrTemplate.php`, `app/Models/OpcrTemplate.php`
- **Bug:** `is_active` is NOT cast to boolean (unlike `IpcrSubmission` which does cast it). Strict comparisons like `$template->is_active === true` fail against the integer `1` from the database.
- **Fix:** Add `'is_active' => 'boolean'` to the `$casts` array in both models.

### 1.5 Dean dashboard is a 404 dead end
- **File:** `resources/views/dashboard/dean/index.blade.php`
- **Bug:** The view calls `abort(404)` — dean-role users hitting `/dean/dashboard` get a 404 error despite having a valid route.
- **Fix:** Implement the actual dean dashboard view (at minimum, a landing page with navigation to review routes).

### 1.6 Director dashboard is an empty stub
- **File:** `resources/views/dashboard/director/Index.blade.php`
- **Bug:** Contains only an empty `<head>` tag and blank body (29 lines of boilerplate). Director users see a blank page. Also, the capital `I` in `Index.blade.php` may cause 500 errors on case-sensitive Linux deployment servers.
- **Fix:** Implement a basic director dashboard view and rename to `index.blade.php` (lowercase).

---

## 2. Security Vulnerabilities

### 2.1 CRITICAL: Command injection in database restore
- **File:** `app/Http/Controllers/Admin/DatabaseManagementController.php` (~line 108) and `app/Services/DatabaseBackupService.php`
- **Vulnerability:** Database password is concatenated into a shell command string passed to `exec()`. If the DB password contains shell metacharacters (e.g., `"; rm -rf /;`), arbitrary OS commands execute. The password is also visible in the OS process list.
- **Fix:** Use `Symfony\Component\Process\Process` with separate arguments (not a shell string), or use `MYSQL_PWD` environment variable, or a MySQL option file (`--defaults-extra-file`). Always use `escapeshellarg()` on any dynamic value passed to shell commands.

### 2.2 CRITICAL: Stored XSS via `table_body_html`
- **Files:** All Template, Submission, and SavedCopy controllers (IPCR + OPCR)
- **Vulnerability:** `table_body_html` accepts raw HTML with no sanitization. If rendered with `{!! !!}` in Blade (which is the expected way to render HTML tables), users can inject `<script>` tags for persistent XSS attacks.
- **Fix:** Sanitize HTML using a library like `HTMLPurifier` (via `mews/purifier` Laravel package) before storage. Allow only safe HTML tags (`<table>`, `<tr>`, `<td>`, `<th>`, `<span>`, etc.) and strip `<script>`, event handlers, and dangerous attributes.

### 2.3 HIGH: IDOR in `SupportingDocumentController::index()`
- **File:** `app/Http/Controllers/Faculty/SupportingDocumentController.php` (~line 32)
- **Vulnerability:** The `owner_id` request parameter lets any authenticated faculty user view another user's supporting documents. No authorization check verifies the requesting user has permission to view.
- **Fix:** Scope the query to `auth()->id()` unless the user has dean/admin role. Add `$this->authorize()` or manual role check.

### 2.4 HIGH: IDOR in `FacultyDashboardController::setProfilePhoto()`
- **File:** `app/Http/Controllers/Dashboard/FacultyDashboardController.php` (~line 313)
- **Vulnerability:** Validates `photo_id` exists in `user_photos` table but doesn't scope to `auth()->id()`. User A can set User B's photo as their own profile photo.
- **Fix:** Change validation rule to `exists:user_photos,id,user_id,` . auth()->id()` or add a manual check after finding the photo.

### 2.5 HIGH: `TrustProxies` trusts all proxies
- **File:** `app/Http/Middleware/TrustProxies.php`
- **Vulnerability:** `$proxies = '*'` trusts ALL proxies, allowing any client to spoof IP addresses via `X-Forwarded-For` headers.
- **Fix:** Set `$proxies` to the actual IP addresses of your load balancer/reverse proxy, or remove the wildcard if not behind a proxy.

### 2.6 MEDIUM: No brute-force protection on password reset code
- **File:** `app/Http/Controllers/PasswordResetController.php`
- **Vulnerability:** The 6-digit verification code has 1,000,000 combinations. With a 15-minute window and no rate limiting on the verify endpoint, an attacker can brute-force it.
- **Fix:** Add `ThrottleRequests` middleware (e.g., `throttle:5,15`) to the verify-code route. invalidate the code after 5 failed attempts.

### 2.7 MEDIUM: Email verification codes stored in plaintext
- **File:** `app/Http/Controllers/EmailVerificationController.php`
- **Vulnerability:** Verification codes stored as plain text in `email_verifications.code`. A database breach exposes all pending codes.
- **Fix:** Hash codes with `Hash::make()` before storage, verify with `Hash::check()`.

### 2.8 MEDIUM: No rate limiting on `sendResetCode()`
- **File:** `app/Http/Controllers/PasswordResetController.php`
- **Vulnerability:** No rate limiting on password reset email requests. Attackers can spam reset emails to any registered user.
- **Fix:** Add `throttle:3,15` middleware to the route or use Laravel's `RateLimiter`.

### 2.9 MEDIUM: SQL backup file upload accepts any content
- **File:** `app/Http/Controllers/Admin/DatabaseManagementController.php` (~line 145)
- **Vulnerability:** Only checks file extension (`.sql`), not content. A malicious PHP file renamed to `.sql` could be uploaded and later executed via `exec()` against MySQL.
- **Fix:** Validate file MIME type, scan first bytes for valid SQL content, and sanitize filename.

### 2.10 LOW: SSRF risk in document download
- **File:** `app/Http/Controllers/Faculty/SupportingDocumentController.php` (~line 231)
- **Vulnerability:** `file_get_contents($document->path)` uses a remote URL with no timeout or URL validation. If `path` were tampered to an internal URL, this becomes SSRF.
- **Fix:** Validate that `$document->path` points to the expected Cloudinary domain. Use `Http::timeout(10)->get()` instead of `file_get_contents()`.

---

## 3. Architectural Redundancies

### 3.1 IPCR/OPCR Model Duplication (6 models → could be 3)
- **Models affected:**
  - `IpcrTemplate` ↔ `OpcrTemplate` (100% structural clone)
  - `IpcrSubmission` ↔ `OpcrSubmission` (100% structural clone)
  - `IpcrSavedCopy` ↔ `OpcrSavedCopy` (100% structural clone)
- **Impact:** Any bug fix or feature must be applied in 2 places. 6 tables with identical schemas.
- **Recommendation:** Either:
  - **(A)** Add a `type` column (`ipcr`/`opcr`) to 3 unified tables (`templates`, `submissions`, `saved_copies`), use a single model per concept with scope methods (`scopeIpcr()`, `scopeOpcr()`).
  - **(B)** Create a shared Trait or abstract base class for each pair (e.g., `HasTemplateFields`) to eliminate field/method duplication while keeping separate tables.
  - Option A is cleaner; Option B is lower risk for an existing system.

### 3.2 IPCR/OPCR Controller Duplication (~750 lines)
- **Controllers affected:**
  - `IpcrTemplateController` ↔ `OpcrTemplateController` (~95% identical, 296 lines each)
  - `IpcrSubmissionController` ↔ `OpcrSubmissionController` (~95% identical, 210 lines each)
  - `IpcrSavedCopyController` ↔ `OpcrSavedCopyController` (~95% identical, 140 lines each)
- **Recommendation:** Extract a generic base controller or service class parameterized by model type. Only the model class name and log strings differ.

### 3.3 Photo Management Duplication
- `FacultyDashboardController` methods (`uploadPhoto`, `getPhotos`, `setProfilePhoto`, `deletePhoto`) duplicate logic in `Admin/PhotoController`.
- **Recommendation:** Both should delegate to `PhotoService` entirely. Faculty controller should not contain photo management logic.

### 3.4 Triple Role System (Data Consistency Risk)
- **Three sources of truth for user roles:**
  1. `users.role` — legacy enum column (still populated, never removed)
  2. `user_roles` table — pivot table with string-based role
  3. `roles` table — role definitions with acronyms and permissions
- The `User::hasRole()` checks `user_roles` only, ignoring `users.role`. But seeders write to both.
- **Recommendation:**
  1. Remove the `users.role` column via migration
  2. Update all seeders to only use `user_roles`
  3. Ensure `user_roles.role` references `roles.name` via foreign key (or migrate to `role_id` integer FK)

### 3.5 Faculty Navigation Markup Duplication (~500 lines)
- The entire navigation bar, mobile menu, hamburger button, and notification popup are copy-pasted across 3 faculty view files.
- **Recommendation:** Create `resources/views/layouts/faculty.blade.php` layout. Extract shared nav into `@section('navigation')` or a Blade component.

### 3.6 Duplicate `RoleMiddleware` Files
- `app/Middleware/RoleMiddleware.php` (orphaned, never loaded) and `app/Http/Middleware/RoleMiddleware.php` (active)
- **Recommendation:** Delete `app/Middleware/RoleMiddleware.php`. The `app/Middleware/` directory is non-standard.

### 3.7 Dead `Kernel.php`
- `app/Http/Kernel.php` is from pre-Laravel 11. In Laravel 11, `bootstrap/app.php` is the authority. Kernel.php is dead code that misleads developers.
- **Recommendation:** Delete `app/Http/Kernel.php` after confirming all middleware registrations exist in `bootstrap/app.php`.

---

## 4. Model Layer Issues

### 4.1 Missing Polymorphic Relationships
- `SupportingDocument` has `documentable_type`/`documentable_id` columns but **no `morphTo()` relationship**.
- `ActivityLog` has `subject_type`/`subject_id` columns but **no `morphTo()` relationship**.
- `IpcrSubmission`, `OpcrSubmission`, `IpcrTemplate`, `OpcrTemplate` have **no `morphMany()` for SupportingDocument**.
- **Fix:** Add `public function documentable() { return $this->morphTo(); }` to `SupportingDocument`. Add `public function subject() { return $this->morphTo(); }` to `ActivityLog`. Add `public function supportingDocuments() { return $this->morphMany(SupportingDocument::class, 'documentable'); }` to all Template/Submission models. Register a `morphMap` in `AppServiceProvider::boot()`.

### 4.2 Missing User Relationships
- `User` model only defines `ipcrTemplates()`. Missing:
  - `ipcrSubmissions()` → hasMany IpcrSubmission
  - `ipcrSavedCopies()` → hasMany IpcrSavedCopy
  - `opcrTemplates()` → hasMany OpcrTemplate
  - `opcrSubmissions()` → hasMany OpcrSubmission
  - `opcrSavedCopies()` → hasMany OpcrSavedCopy
  - `supportingDocuments()` → hasMany SupportingDocument
  - `activityLogs()` → hasMany ActivityLog
- **Fix:** Add all missing hasMany relationships to the User model.

### 4.3 `User::roles()` is not a real Eloquent relationship
- `User::roles()` does `$this->userRoles()->pluck('role')->toArray()` — a method that executes a query every call. It cannot be eager-loaded. Every call to `hasRole()`, `hasAnyRole()`, `getPrimaryRole()` fires a DB query.
- **Impact:** Critical N+1 risk when iterating over users.
- **Fix:** Convert to a proper `belongsToMany` relationship via `user_roles` pivot, or cache the result per-instance (e.g., memoization). Example: `public function roles() { return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role', 'id', 'name'); }`

### 4.4 `User::getProfilePhotoUrlAttribute()` bypasses eager-loaded relationship
- Methods `getProfilePhotoUrlAttribute()` and `hasProfilePhoto()` both execute raw `UserPhoto::where(...)` queries instead of using the defined `$this->profilePhoto` relationship. Even if `profilePhoto` is eager-loaded, these methods fire new queries.
- **Fix:** Rewrite both methods to use `$this->profilePhoto` (the relationship):
  ```php
  public function getProfilePhotoUrlAttribute() {
      $photo = $this->profilePhoto;
      return $photo?->path ?? null;
  }
  ```

### 4.5 Redundant timestamp casts on Template models
- `IpcrTemplate` and `OpcrTemplate` cast `created_at` and `updated_at` to `datetime`, which Eloquent already does by default.
- **Fix:** Remove these casts — they're unnecessary.

### 4.6 `UserRole` has no relationship to `Role` model
- `UserRole` stores a string `role` name but has no `belongsTo` to the `Role` model.
- **Fix:** Add `public function roleModel() { return $this->belongsTo(Role::class, 'role', 'name'); }`.

### 4.7 Mass assignment concerns
- `ActivityLog`: `created_at` is in `$fillable` — allows timestamp spoofing.
- `User`: `last_login_at` and `is_active` are in `$fillable` — should be set programmatically only.
- `SupportingDocument`: `documentable_type` in `$fillable` — allows arbitrary morph type injection.
- **Fix:** Remove these from `$fillable` and set them explicitly in service code.

### 4.8 `UserPhoto::getPhotoUrlAttribute()` is a no-op accessor
- Simply returns `$this->path` — adds no value. Can be accessed directly.
- **Fix:** Either remove the accessor, or add actual logic (e.g., Cloudinary URL transformation).

---

## 5. Controller Layer Issues

### 5.1 No database transactions on multi-step writes
- **Affected controllers:**
  - `UserManagementController::store()` — user creation + role assignment
  - `UserManagementController::update()` — user update + role sync
  - `UserManagementController::destroy()` — user delete + photo delete
  - `IpcrSubmissionController::store()` — deactivate all + create + copy docs
  - `IpcrTemplateController::setActive()` — deactivate all + activate one
  - All OPCR equivalents
- **Risk:** Partial failure leaves data in inconsistent state (e.g., user created but roles not assigned).
- **Fix:** Wrap all multi-step operations in `DB::transaction(function() { ... })`.

### 5.2 Race conditions in `setActive()` patterns
- All `setActive()` methods (templates and submissions, IPCR and OPCR) mass-update to `is_active=false` then set one to `true`. Two concurrent requests can both succeed, leaving multiple active records.
- **Fix:** Wrap in a DB transaction with a `FOR UPDATE` lock, or use a single atomic query.

### 5.3 Inconsistent error handling
- Template controllers wrap everything in `try/catch(\Exception)` which swallows `ModelNotFoundException` as 500 errors.
- Submission controllers have NO try/catch at all — `firstOrFail()` throws raw 404s.
- **Fix:** Use a consistent approach: either let `ModelNotFoundException` propagate naturally (returns 404 via exception handler), or catch it specifically. Never catch generic `\Exception` except at the outermost layer.

### 5.4 Inconsistent JSON response format
- Some methods return `{success: true, data: ...}`.
- `IpcrSubmissionController::store()` returns `{message, id}` (no `success` key).
- `show()` returns `{success, submission}`.
- **Fix:** Standardize on a consistent response envelope: `{success: bool, message: string, data?: any}`.

### 5.5 Debug logging left in production code
- `IpcrTemplateController::store()` and `update()` contain `\Log::info('==== IPCR STORE REQUEST ====')` and dump full request payloads including large HTML.
- **Fix:** Remove all debug logging, or move to debug level behind a feature flag.

### 5.6 Hardcoded admin employee ID
- `UserManagementController` methods use `$user->employee_id === 'URS26-ADM00001'` as a hardcoded magic string for the protected admin.
- **Fix:** Move to a config value (e.g., `config('app.protected_admin_employee_id')`) or use a database flag (`is_super_admin`).

### 5.7 `AdminDashboardController::index()` loads all submissions without pagination
- Fetches ALL `IpcrSubmission` records into memory. Degrades severely at scale.
- **Fix:** Add pagination or limit results.

### 5.8 DOM parsing business logic in controller
- `FacultyDashboardController::index()` has 40+ lines of DOM parsing to extract SO counts from `table_body_html` by CSS class names. This is fragile and belongs in a service.
- **Fix:** Move to a `SubmissionAnalysisService` and prefer the `so_count_json` data over parsing HTML.

### 5.9 `storeFromSavedCopy` uses MySQL-specific binary comparison
- `IpcrTemplateController::storeFromSavedCopy()` uses `whereRaw('BINARY title = ?')` for case-sensitive matching. This breaks on PostgreSQL/SQLite.
- **Fix:** Use Laravel's `whereRaw('LOWER(title) = ?', [strtolower($title)])` or a case-sensitive collation migration.

### 5.10 `SupportingDocumentController::download()` has no timeout
- `file_get_contents($document->path)` on a remote Cloudinary URL with no timeout. Can hang indefinitely.
- **Fix:** Use `Http::timeout(15)->get($document->path)`.

---

## 6. Middleware Issues

### 6.1 `RedirectIfAuthenticated` has no HR role redirect
- HR users fall through with no redirect when already authenticated.
- **Fix:** Add a case for `hr` role that redirects to the appropriate dashboard.

### 6.2 `RedirectIfAuthenticated` redirects dean to faculty dashboard
- Dean users are redirected to `faculty.dashboard` instead of `dean.dashboard`.
- **Fix:** Add dean-specific redirect case.

### 6.3 Active `RoleMiddleware` lost the dean-auto-promotion logic
- The orphaned `app/Middleware/RoleMiddleware.php` had logic to auto-allow dean users through faculty gates. The active version at `app/Http/Middleware/RoleMiddleware.php` does not.
- **Impact:** If any OPCR routes (which use `role:faculty` middleware) should also allow dean users, they'll be 403'd.
- **Fix:** Restore dean-auto-promotion logic in the active middleware if deans need faculty route access, or update OPCR routes to use `role:faculty,dean`.

### 6.4 Permission middleware has redundant admin bypass
- `PermissionMiddleware` checks `$user->hasRole('admin')` explicitly AND `User::hasPermission()` also has an admin bypass. Double check is harmless but redundant.
- **Fix:** Remove one of the two admin bypass checks for clarity.

### 6.5 `LogPageNavigation` performance concern
- Inserts a database row on every tracked GET request. No retention policy, no async queue.
- Full query strings (possibly containing tokens) are persisted in `properties`.
- **Fix:** Dispatch to a queue (`ShouldQueue`), add a scheduled cleanup job, and strip sensitive query parameters.

---

## 7. Service Layer Issues

### 7.1 `ActivityLogService` is entirely static
- Cannot be mocked in tests or dependency-injected.
- Manually sets `created_at` unnecessarily (Eloquent handles it).
- **Fix:** Convert to a regular class with instance methods. Register as a singleton in `AppServiceProvider`. Remove manual `created_at` assignment.

### 7.2 `EmployeeIdService` has race condition
- `random_int(0, 99999)` + existence check is not atomic. Two simultaneous requests can generate the same ID.
- **Fix:** Use a database unique constraint + retry loop with DB-level lock, or use atomic increment.

### 7.3 `EmployeeIdService::updateDepartmentCode()` changes year
- Changes the year portion to current year on department change — silently alters an employee's identity.
- **Fix:** Preserve the original year when updating department code.

### 7.4 `EmployeeIdService` has duplicated generation logic
- `generate()` and `generateForHrOrDirector()` contain nearly identical retry loops.
- **Fix:** Extract shared retry logic into a private helper.

### 7.5 `DatabaseBackupService` exposes DB password on command line
- Password is visible in OS process list when passed via `--password="..."`.
- **Fix:** Use `MYSQL_PWD` environment variable or a MySQL option file.

### 7.6 `PhotoService::setAsProfilePhoto()` not wrapped in transaction
- Unsets previous profile photos, then sets new one. If the second step fails, no photo is marked as profile.
- **Fix:** Wrap in `DB::transaction()`.

### 7.7 Missing services for business logic
- The following logic exists in controllers and should be extracted:
  - DOM parsing / SO count analysis → `SubmissionAnalysisService`
  - Supporting document copying → `SupportingDocumentService`
  - Activity log export formatting → `LogExportService`
  - Shell command construction for DB operations → keep in `DatabaseBackupService` but add the restore logic

---

## 8. Migration & Seeder Issues

### 8.1 Legacy `users.role` enum column never removed
- `create_users_table` migration defines `enum('role', [...])` which is superseded by the `user_roles` table.
- **Fix:** Create a new migration to drop the `role` column from `users`.

### 8.2 `user_roles.role` altered with raw MySQL DDL
- Migration `2026_02_25_000002_change_user_roles_role_to_string.php` uses raw `ALTER TABLE` SQL. Breaks on PostgreSQL/SQLite.
- **Fix:** Replace with `Schema::table('user_roles', function ($table) { $table->string('role')->change(); })` using the `doctrine/dbal` package.

### 8.3 Empty no-op migration
- `2026_02_04_233858_add_is_active_to_ipcr_templates_table.php` has empty `up()` and `down()` methods. Dead code.
- **Fix:** Delete this migration file (safe since `up()` does nothing).

### 8.4 Data seeded inside migrations
- `create_roles_table`, `create_permissions_table`, `create_role_permissions_table` all seed data in `up()`.
- **Problem:** `migrate:rollback` + `migrate` causes duplicate key errors.
- **Fix:** Move seed data to proper seeder classes. Use `upsert()` or check existence first.

### 8.5 `email_verifications` table has no expiry column
- Notification says "30 minutes" but there's no `expires_at` timestamp.
- **Fix:** Add `expires_at` column, set it on creation, and check it during verification.

### 8.6 Missing indexes on frequently queried columns
- `ipcr_submissions.status`, `opcr_submissions.status` — filtered frequently, no index.
- `activity_logs.action` — used with `scopeByAction`, no index.
- **Fix:** Add indexes via new migration.

### 8.7 Seeders are not idempotent
- `DepartmentSeeder`, `DesignationSeeder`, `UserSeeder` use raw `DB::table()->insert()`. Re-running causes duplicate key errors.
- **Fix:** Use `upsert()` or `firstOrCreate()` patterns.

### 8.8 Seeders use hardcoded department IDs
- `UserSeeder` uses `department_id => 3` assuming CCS is ID 3. If departments are seeded differently, this breaks.
- **Fix:** Look up department by code: `Department::where('code', 'CCS')->first()->id`.

---

## 9. Frontend & View Issues

### 9.1 CRITICAL: Tailwind CDN loaded in every view
- Every view includes `<script src="https://cdn.tailwindcss.com"></script>` — the CDN play script. The project already has Tailwind v4 via `@tailwindcss/vite` in the Vite build.
- **Impact:** Tailwind CSS is processed at runtime in the browser (development-only approach), causing performance penalty and larger page loads.
- **Fix:** Remove all Tailwind CDN `<script>` tags. Ensure `@vite(['resources/css/app.css'])` (or the appropriate CSS entry point) is loaded instead, using the compiled Tailwind output.

### 9.2 HIGH: No faculty layout — massive markup duplication
- All 3 faculty view files duplicate ~130 lines of navigation bar, mobile menu, hamburger, notification popup.
- **Fix:** Create `resources/views/layouts/faculty.blade.php` with shared navigation. Convert faculty views to extend it.

### 9.3 HIGH: `my-ipcrs.blade.php` is 5,715 lines
- One single Blade file with 5,715 lines — impossible to maintain.
- **Fix:** Decompose into Blade components/partials:
  - `components/ipcr-template-card.blade.php`
  - `components/submission-card.blade.php`
  - `components/saved-copy-card.blade.php`
  - `components/modal/*.blade.php` (each modal)
  - `partials/ipcr-tab.blade.php`, `partials/opcr-tab.blade.php`

### 9.4 HIGH: No error pages
- No `resources/views/errors/` directory. Users see default Laravel error pages for 404, 403, 500, 503, 419.
- **Fix:** Create custom error pages that match the app's design.

### 9.5 HIGH: Zero ARIA attributes or accessibility support
- No `aria-*`, no `role` attributes, no skip-to-content links, no keyboard navigation for modals, no focus management.
- **Fix:** Systematic accessibility pass across all views. Priority: modals (role="dialog", aria-modal, focus trap), navigation (aria-label, aria-expanded), form labels, color-contrast.

### 9.6 MEDIUM: Notifications are hardcoded fakes
- All notification popups show 3 static placeholder notifications.
- **Fix:** Replace with a real notification system (database notifications with polling or WebSocket).

### 9.7 MEDIUM: Font Awesome version mismatch
- Admin layout loads v6.5.1, faculty/auth pages load v6.4.0.
- **Fix:** Normalize to one version across all views, preferably via npm.

### 9.8 MEDIUM: Commented-out JS files in Vite config
- Several JS files exist in `resources/js/` but are commented out in `vite.config.js`. Dead code.
- **Fix:** Either uncomment and use them, or delete the files.

### 9.9 MEDIUM: JavaScript global scope pollution
- Functions assigned to `window.*` instead of using module imports/exports.
- Shared functions (toggleMobileMenu, toggleNotificationPopup, etc.) duplicated across JS files.
- **Fix:** Extract shared functions into a `resources/js/shared.js` module and import them.

### 9.10 LOW: `console.log` statements in production JS
- Debug `console.log` calls left in production JavaScript code.
- **Fix:** Remove all `console.log` statements or use a build-time strip.

### 9.11 LOW: Inline `<script>` blocks in views
- Several admin views have inline `<script>` blocks alongside Vite-bundled scripts.
- **Fix:** Move all inline scripts to their respective Vite entry point JS files.

### 9.12 Hardcoded values that should be configurable
| Value | Location | Recommendation |
|-------|----------|----------------|
| `"University of Rizal System Binangonan"` | Every auth page title | `config('app.university_name')` |
| `"URS26-ADM00001"` (protected admin) | users index view | `config('app.protected_admin_id')` |
| Deadline dates (`Jul 15`, `Jul 31`) | Faculty dashboard sidebar | Database or settings table |
| `"PCHS Dean"` in notification text | Faculty views | Dynamic from database |
| `"Max 5MB"` photo size text | User edit form | Match server validation value |

---

## 10. Route Issues

### 10.1 OPCR routes use `role:faculty` middleware but require dean permissions
- All OPCR template/submission/saved-copy routes have `middleware(['auth', 'role:faculty', 'permission:dean.opcr.*'])`.
- The `role:faculty` middleware checks the user's role, and the active version does NOT auto-promote dean users. A dean user would fail the `role:faculty` check before getting to the `permission:dean.opcr.*` check.
- **Fix:** Change to `role:faculty,dean` or restore dean-auto-promotion in the active middleware.

### 10.2 `SupportingDocumentController::download()` route only allows `role:faculty`
- The controller checks for `dean` and `admin` roles internally, but the route middleware only allows `role:faculty`. Dean/admin users cannot reach this endpoint.
- **Fix:** Update route middleware to `role:faculty,dean,admin` or remove the controller-level role check.

### 10.3 Some routes lack `->name()` definitions
- Several template/submission routes (show, destroy, update, setActive) have no named route.
- **Impact:** Cannot use `route()` helper; hardcoded URLs are required.
- **Fix:** Add `->name()` to all routes for consistency.

### 10.4 Routes are not grouped efficiently
- Faculty IPCR/OPCR routes repeat `middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.templates'])` on every single route instead of using a middleware group.
- **Fix:** Use `Route::middleware([...])->prefix('faculty/ipcr')->group(function() { ... })`.

---

## 11. Notification Issues

### 11.1 Triple `->salutation()` bug
- **Files:** Both `EmailVerificationNotification.php` and `PasswordResetNotification.php`
- **Bug:** `->salutation()` is called 3 times in sequence. Each call replaces the previous. Only the last one shows.
- **Fix:** Use `->salutation("Best regards,\nUniversity of Rizal System Binangonan\nIPCR System Team")` — single call with newlines.

### 11.2 `PasswordResetNotification` has debug placeholder
- **File:** `app/Notifications/PasswordResetNotification.php` (line ~50)
- **Bug:** Final salutation is `'The John James'` — a placeholder/joke that was never removed.
- **Fix:** Replace with proper institutional name.

### 11.3 `ShouldQueue` imported but not implemented
- Both notification classes import `ShouldQueue` and use the `Queueable` trait, but neither class `implements ShouldQueue`. Emails are sent synchronously, blocking HTTP requests.
- **Fix:** Either add `implements ShouldQueue` to the class (recommended) or remove the unused imports.

---

## 12. Missing Features

### 12.1 `AppServiceProvider` is minimal
- No `Model::preventLazyLoading()` in development — N+1 issues go undetected.
- No `Model::preventSilentlyDiscardingAttributes()` — mass assignment typos are silent.
- No morph map registration for polymorphic relationships.
- **Fix:** Add these to `boot()`:
  ```php
  Model::preventLazyLoading(!app()->isProduction());
  Model::preventSilentlyDiscardingAttributes(!app()->isProduction());
  Relation::enforceMorphMap([
      'ipcr_submission' => IpcrSubmission::class,
      'opcr_submission' => OpcrSubmission::class,
      // etc.
  ]);
  ```

### 12.2 No database backup retention/cleanup
- Backups accumulate in `storage/app/backups/` forever.
- **Fix:** Add a scheduled command to delete backups older than N days.

### 12.3 No queued job processing
- Notifications use `ShouldQueue` imports but don't implement it. No queue worker configured.
- **Fix:** Implement `ShouldQueue` on notifications and set up a queue driver.

### 12.4 No tests
- `tests/Feature/` and `tests/Unit/` are empty.
- **Fix:** Add at minimum: authentication tests, RBAC tests, template/submission CRUD tests, and authorization tests for the IDOR vulnerabilities identified above.

---

## 13. Recommended Implementation Order

Prioritized by impact and risk. Each numbered item is an independent work unit.

### Phase 1: Critical Bug Fixes (Do First)
1. **Fix `RedirectIfAuthenticated::guard()` bug** — causes 500 for all authenticated users on guest routes
2. **Fix `PhotoService::env()` bug** — breaks all photo uploads in production
3. **Fix `PasswordResetNotification::first_name/last_name`** — broken password reset emails
4. **Add `is_active` boolean cast** to `IpcrTemplate` and `OpcrTemplate`
5. **Fix notification salutation bugs** and remove `'The John James'` placeholder

### Phase 2: Security Patches
6. **Fix command injection** in `DatabaseBackupService` / `DatabaseManagementController::restore()`
7. **Add HTML sanitization** for `table_body_html` (all template/submission stores)
8. **Fix IDOR** in `SupportingDocumentController::index()` and `FacultyDashboardController::setProfilePhoto()`
9. **Fix `TrustProxies`** — remove wildcard
10. **Add rate limiting** to password reset and verification endpoints
11. **Hash email verification codes** before storage

### Phase 3: Data Integrity
12. **Add `DB::transaction()`** to all multi-step write operations
13. **Fix race conditions** in `setActive()` methods
14. **Remove legacy `users.role` column** via migration
15. **Convert `User::roles()`** to a proper eager-loadable relationship
16. **Fix `User::getProfilePhotoUrlAttribute()`** to use relationship instead of raw query
17. **Add missing model relationships** (User hasMany, morph relationships)

### Phase 4: Architecture Cleanup
18. **Remove dead files** — `app/Middleware/RoleMiddleware.php`, `app/Http/Kernel.php`, empty migration
19. **Fix route middleware** for OPCR routes (dean access) and supporting document download
20. **Refactor IPCR/OPCR duplication** — extract shared controller/model logic
21. **Move business logic** from controllers to services

### Phase 5: Frontend
22. **Remove Tailwind CDN** from all views — use Vite build
23. **Create faculty layout** to eliminate navigation duplication
24. **Decompose `my-ipcrs.blade.php`** into components
25. **Add error pages** (404, 403, 500, 419, 503)
26. **Implement dean and director dashboards**
27. **Consolidate JS** — extract shared modules
28. **Add accessibility** (ARIA, focus management, keyboard navigation)

### Phase 6: Polish
29. **Standardize JSON response format** across all controllers
30. **Add database indexes** for status columns
31. **Make seeders idempotent** (use `firstOrCreate`)
32. **Replace fake notifications** with real system
33. **Add tests** for critical paths
34. **Remove `console.log` and debug logging**
35. **Configure `AppServiceProvider`** with `preventLazyLoading`, `morphMap`, etc.

---

*End of audit. Each item includes enough context for an AI agent to locate the affected files and implement the fix.*
