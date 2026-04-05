# 🎓 IPCR System

A comprehensive Individual Performance Commitment and Review (IPCR) management system built with Laravel, designed for educational institutions to track and manage faculty performance evaluations.

## ✨ Features

- 🔐 **Multi-Role Authentication** (Admin, Director, Dean, Faculty)
- 📧 **Password Reset via Email** with 6-digit verification codes
- ✅ **Email Verification System** with Brevo integration and auto-reset on email change
- 📊 **Performance Dashboard** with charts and metrics
- 📱 **Fully Responsive Design** (Mobile, Tablet, Desktop)
- 🔔 **Notification System** with real-time updates
- 👤 **User Profile Management** with photo uploads via Cloudinary CDN
- 📈 **Profile Completeness Tracking** with 8 monitored fields and visual progress
- 🆔 **Automatic Employee ID Generation**
- 🔑 **Password Management** with secure hashing
- ⏰ **Last Login Tracking**
- 🌏 **Hong Kong Timezone (Asia/Hong_Kong)**
- ⚡ **Vite Asset Bundling** with optimized CSS/JS
- 🎨 **Tailwind CSS v4.0** for modern styling
- 📸 **Cloudinary Photo Management** with crop, resize, and CDN delivery

## ✨ Latest Changes (February 27, 2026)

### 🆕 New Files Added

#### 🔐 Role & Permission Management System
A brand-new Role & Permission management module has been added to the admin panel.

| File | Description |
|------|-------------|
| `app/Http/Controllers/Admin/RoleDesignationController.php` | New controller handling CRUD for roles and designations |
| `app/Models/Permission.php` | New Eloquent model for the `permissions` table |
| `app/Models/Role.php` | New Eloquent model for the `roles` table |
| `app/Http/Middleware/PermissionMiddleware.php` | New middleware for granular, permission-based route protection |

#### 🗄️ New Database Migrations

| Migration File | Description |
|------|-------------|
| `database/migrations/2026_02_25_000001_create_roles_table.php` | Creates the `roles` table |
| `database/migrations/2026_02_25_000002_change_user_roles_role_to_string.php` | Alters the `user_roles` table to store role as a string reference |
| `database/migrations/2026_02_25_100001_create_permissions_table.php` | Creates the `permissions` table |
| `database/migrations/2026_02_25_100002_create_role_permissions_table.php` | Creates the `role_permissions` pivot table |

#### 🎨 New Views – Role Management
- `resources/views/admin/role-management/` — New directory containing all Blade views for the Role & Designation management section

---

### 🔄 Modified Files

#### 🧩 Backend – Controllers & Models

| File | What Changed |
|------|-------------|
| `app/Http/Controllers/Admin/ActivityLogController.php` | Added export-to-text-file feature with date-range, user, and action filters |
| `app/Http/Controllers/Admin/UserManagementController.php` | Refactored to support inline modal data APIs (view & edit) via AJAX |
| `app/Models/User.php` | Extended with role/permission relationship methods for the new RBAC system |
| `app/Services/EmployeeIdService.php` | Updated employee ID generation logic to align with new role/department structure |

#### ⚙️ Application Bootstrap & Routing

| File | What Changed |
|------|-------------|
| `bootstrap/app.php` | Registered `PermissionMiddleware` in the application middleware stack |
| `routes/web.php` | Added new routes for role management, permission checks, and activity log export |

#### 🎨 CSS Stylesheets

| File | What Changed |
|------|-------------|
| `resources/css/admin_layout.css` | Polish updates: glassmorphic tab navigation, updated header typography, standardized table containers |
| `resources/css/dashboard_faculty_index.css` | Refined faculty dashboard metric cards, progress bar, and section styles |

#### 📜 JavaScript

| File | What Changed |
|------|-------------|
| `resources/js/admin_layout.js` | Added AJAX modal handlers for viewing and editing users inline (no page reload) |
| `resources/js/admin_users_index.js` | Updated to open/populate view & edit modals dynamically with fetched user data |

#### 🖼️ Blade Views – Admin

| File | What Changed |
|------|-------------|
| `resources/views/admin/activity-logs/index.blade.php` | Added export modal UI with date range, user, and action filters |
| `resources/views/admin/database/index.blade.php` | UI refinements for consistency with updated admin layout |
| `resources/views/admin/users/edit.blade.php` | Retained as standalone page; updated for modal compatibility |
| `resources/views/admin/users/index.blade.php` | Added inline view & edit modal markup; removed standalone page redirects |
| `resources/views/admin/users/show.blade.php` | Updated to serve as both standalone page and modal content source |

#### 🖼️ Blade Views – Dashboards

| File | What Changed |
|------|-------------|
| `resources/views/dashboard/admin/index.blade.php` | Updated dashboard metrics and UI polish |
| `resources/views/dashboard/director/Index.blade.php` | Minor layout and styling updates |
| `resources/views/dashboard/faculty/index.blade.php` | Redesigned metric cards, IPCR progress bar, performance overview, and notification sections |
| `resources/views/dashboard/faculty/my-ipcrs.blade.php` | UI and interaction improvements to the My IPCRs listing page |
| `resources/views/dashboard/faculty/profile.blade.php` | Profile page styling and section refinements |

#### 🏗️ Shared Layout

| File | What Changed |
|------|-------------|
| `resources/views/layouts/admin.blade.php` | Updated sidebar navigation to include the new Role Management link and section |

#### 📦 Build Manifest

| File | What Changed |
|------|-------------|
| `public/build/manifest.json` | Regenerated after new assets were added and existing assets were modified |

---

## ✨ Previous Updates (February 14, 2026)

### 🚀 Modular Admin Layout & SPA Navigation (February 14, 2026)

#### Single Page Application (SPA) Experience
- ⚡ **Turbo.js Integration** - Implemented `@hotwired/turbo` for seamless "no-reload" navigation
- 🔄 **Instant Transitions** - Switching between Dashboard, Users, and Database is now instant
- 💾 **State Persistence** - Admin layout remains loaded while content swaps dynamically
- 🧩 **Smart Re-initialization** - JS logic (modals, search, filters) automatically re-attaches on navigation

#### Modular Architecture
- 🏗️ **Shared Layout System** - Unified `layouts/admin.blade.php` for all admin pages
- 🎨 **Consolidated Assets** - Merged all admin CSS into `admin_layout.css` (reduced requests)
- 📜 **Unified Logic** - Centralized all admin JS in `admin_layout.js`
- 📱 **Enhanced Responsiveness** - Improved sidebar toggling and mobile overlay behavior (XL breakpoint)
- 🧹 **Code Cleanup** - Removed redundant page-specific assets and duplicated HTML

#### Files Refactored
- **Layout:** `resources/views/layouts/admin.blade.php` (Master layout)
- **Styles:** `resources/css/admin_layout.css` (single source of truth)
- **Scripts:** `resources/js/admin_layout.js` (with `turbo:load` listeners)
- **Views:** Refactored Dashboard, User Management, and Database Management to extend master layout
- **Config:** Updated `vite.config.js` to serve consolidated assets

### 👤 Profile Management Overhaul (February 9-10, 2026)

#### Profile Completeness System
- ✅ **Dynamic Calculation** - Real-time profile completeness tracking with 8 fields monitored
- 📊 **Tracked Fields** - Name, Email, Username, Phone, Employee ID, Department, Designation, Profile Photo
- 🎨 **Compact Progress Bar** - Replaced circular SVG with slim horizontal progress bar
- 📋 **Expandable Checklist** - "Show details" toggle reveals field-by-field completion status
- 🎨 **Color-Coded Progress** - Green (100%), Yellow (50-99%), Red (0-49%)
- 🔢 **Percentage Display** - Shows completion percentage and completed/total ratio
- 💾 **User Model Methods** - `getProfileCompleteness()` and `getCompletenessColor()` for reusable logic

#### Email Verification System
- 📧 **Brevo Integration** - Email verification codes sent via Brevo SMTP
- 🔢 **6-Digit Codes** - Secure random codes with 30-minute expiration
- ⌨️ **Individual Digit Inputs** - Modern UX with 6 separate input boxes
- ⚡ **Auto-Focus & Auto-Submit** - Automatic focus advance and submission when complete
- 📋 **Paste Support** - Paste full code to auto-distribute across boxes
- ⏱️ **Rate Limiting** - 1-minute cooldown between verification code requests
- 🎨 **Visual States** - Filled (green), error (red shake animation), focus states
- 🔄 **Two-Step Modal** - Request code → Enter code with back/resend options
- 🔒 **Database Table** - `email_verifications` table stores codes with user_id and timestamps
- 📁 **New Files Created:**
  - `app/Http/Controllers/EmailVerificationController.php` - Send and verify codes
  - `app/Notifications/EmailVerificationNotification.php` - Email template
  - Migration: `2026_02_09_192529_create_email_verifications_table.php`
  - Routes: `/email/verification/send` and `/email/verification/verify`

#### Email Change Detection & Re-verification
- 🔄 **Automatic Reset** - Changing email in profile clears `email_verified_at` timestamp
- 🗑️ **Code Cleanup** - Old verification codes deleted when email changes
- 🔔 **Auto-Prompt** - Verification modal opens automatically after email update
- 💾 **Session Storage** - Uses sessionStorage to trigger modal after page reload
- 🎯 **User Message** - "Profile updated. Please verify your new email address."

#### Profile Page Complete Redesign
- 🎨 **Modern Layout** - Full-width header card with compact sections
- 📸 **Photo with Camera Button** - Inline photo upload access with overlay button
- 🏷️ **Badge Integration** - Roles and status displayed as inline badges in header
- 📊 **Activity in Header** - Last login, member since, photos moved to top-right sidebar
- 🔐 **Unified Security Section** - Edit Profile, Change Password, Manage Photos, Email Verification, and Roles in one card
- 🎨 **Icon-Based Actions** - Colored circle icons for each action (blue, amber, purple, green, indigo)
- ➡️ **Chevron Navigation** - Hover effects with right-pointing chevrons
- 🧹 **Eliminated Redundancies:**
  - Removed duplicate "Roles" from Work Information (kept in header + security)
  - Removed duplicate "Account Status" from Work Information (kept as badge)
  - Removed duplicate "Member Since" from Work Information (kept in activity)
  - Consolidated all actions into Account & Security section
- 📱 **Mobile Responsive** - Optimized layout for all screen sizes

#### Photo Gallery Cloudinary Integration
- ☁️ **Cloudinary CDN** - Photos now served from Cloudinary instead of local storage
- 🔧 **Model Update** - `UserPhoto->photo_url` accessor returns `path` column (Cloudinary URL)
- 🖼️ **Gallery Fix** - Photo gallery modal now displays images from Cloudinary API
- 🔗 **Direct URLs** - No more asset() wrapper, uses direct Cloudinary secure_url
- 📦 **Existing Upload** - PhotoService already uploading to Cloudinary (now retrieval matches)

#### Technical Implementation
- 📁 **JavaScript Updates:**
  - `toggleCompletenessDetails()` - Toggle checklist visibility with animation
  - Chevron rotation on toggle (up/down)
  - Session storage for email verification prompt
- 🎨 **CSS Additions:**
  - `.completeness-bar` - Progress bar with 1s ease-in-out transition
  - `.animate-slideDown` - Smooth expand animation for checklist (0.3s)
  - `.completeness-chevron` - Rotation transform for toggle icon
- 🔄 **Controller Updates:**
  - Email change detection in `FacultyDashboardController->updateProfile()`
  - Returns `email_changed` flag to frontend
  - Explicit `email_verified_at = null` on email update
- 🗄️ **Database Changes:**
  - New table: `email_verifications` (user_id, code, timestamps)
  - Uses DB facade for code management (create, check, delete)

#### Files Modified/Created
**New Files:**
- `app/Http/Controllers/EmailVerificationController.php` - Verification logic
- `app/Notifications/EmailVerificationNotification.php` - Email template
- `database/migrations/2026_02_09_192529_create_email_verifications_table.php`

**Modified Files:**
- `app/Models/User.php` - Added `getProfileCompleteness()` and `getCompletenessColor()`
- `app/Models/UserPhoto.php` - Updated `photo_url` accessor for Cloudinary URLs
- `app/Http/Controllers/Dashboard/FacultyDashboardController.php` - Email change detection
- `resources/views/dashboard/faculty/profile.blade.php` - Complete layout redesign
- `resources/js/dashboard_faculty_profile.js` - Toggle function and email verification
- `resources/css/dashboard_faculty_profile.css` - Progress bar animations
- `routes/web.php` - Added email verification routes (POST /email/verification/send, /verify)

---

## 📦 Previous Updates

### ‍🏫 Dean Review & Calibration System (February 9, 2026)
- ✨ **Faculty IPCR Review** - Deans can view all IPCR submissions from faculty in their department
- 🏛️ **Department-Based Filtering** - Automatic filtering shows only relevant department submissions
- 🔄 **Cross-Dean Calibration** - Deans can view and calibrate other deans' IPCR submissions
- 📊 **Dedicated Sidebar Sections** - "Faculty IPCRs" (indigo) and "Dean Calibration" (amber) sections
- 🔒 **Read-Only Access** - Dean review mode displays submissions with no edit capabilities
- 🎨 **Color-Coded UI** - Indigo theme for faculty reviews, amber theme for dean calibration
- 👤 **User Information Display** - Shows submitter name, employee ID, title, year/semester, status
- 🏢 **Department Badges** - Dean calibration cards show department codes (CSS, COA, COB, etc.)
- 📅 **Submission Tracking** - Displays submission dates for each IPCR
- 🔐 **Role-Based Access** - Only users with dean role can access review features
- ⚡ **Auto-Load on Page Load** - Both sections populate automatically when page loads
- 🎯 **Template Preview Modal** - Reuses existing modal for consistent viewing experience

### 🔧 Backend Architecture for Dean Review (February 9, 2026)
- 📁 **DeanReviewController** - New controller with 4 API endpoints for dean review functionality
- 🛣️ **Protected Routes** - All dean review routes under `auth, role:dean` middleware
- 🔗 **Eloquent Relationships** - Utilizes User->department and IpcrSubmission->user relationships
- 📊 **Efficient Queries** - Eager loading with `with()` for optimal performance
- 🔒 **Security Filters** - Department-based and role-based query filtering
- 📅 **Formatted Dates** - Submission dates formatted as "M d, Y" (e.g., "Feb 09, 2026")
- 🆔 **Employee ID Integration** - Displays employee IDs alongside user names

### 📊 Dashboard Analytics & SO Tracking (February 5, 2026)
- ✨ **Strategic Objectives Counter** - Automatically counts SOs from active template per section
- 📈 **Dynamic Progress Tracking** - Dashboard shows accomplished/total ratio (e.g., 0/3, 1/3)
- 🔢 **Smart Percentage Calculation** - Auto-calculates completion rates (0/3 = 0%, 1/3 = 33%)
- 📊 **N/A Display Logic** - Shows "N/A" when no template is active (0/0 scenario)
- 🎯 **Section-wise Metrics** - Separate tracking for Strategic Objectives, Core Functions, Support Functions
- 💾 **JSON Storage** - SO counts stored in database for faster retrieval
- 🔄 **Auto-sync with Template** - Dashboard updates automatically when template is set as active

### 🎨 Template Management Enhancements (February 5, 2026)
- ✅ **SO Numbering Fix** - Roman numerals now increment correctly (I, II, III... XVI, XVII...)
- 🔁 **Section Reset Logic** - SO numbering resets when starting a new section
- ♾️ **Unlimited Roman Numerals** - Support for any number beyond XV using conversion algorithm
- 💾 **Save Copy Feature** - Save templates as drafts in "Saved Copy" section from preview modal
- 🎯 **Preview Modal Integration** - "Save Copy" button available when viewing templates
- 📋 **Draft Management** - Saved copies appear in dedicated section, separate from templates
- 🗂️ **Copy vs Template** - Clear distinction between reusable templates and working drafts

### ⚡ Build System Optimization (February 5, 2026)
- 📦 **Code Extraction** - Moved inline CSS/JS from 5 blade files to external assets
- 🎨 **New CSS Files Added:**
  - `resources/css/auth_verify-code.css` - Code input styling and animations
  - `resources/css/auth_reset-password.css` - Password reset form styling
  - `resources/css/admin_users_create.css` - Create user page styles
- 📜 **New JS Files Added:**
  - `resources/js/auth_verify-code.js` - Verification code input handling
  - `resources/js/auth_reset-password.js` - Password toggle functionality
  - `resources/js/admin_users_create.js` - User creation form interactions
- 🔧 **Vite Configuration** - Updated to bundle 23 total assets (6 new files)
- 📦 **Optimized Builds** - All assets now properly minified and cache-busted
- ✅ **Clean Blade Templates** - Removed ~200+ lines of inline code across 5 files

### 🗄️ Database Schema Updates (February 5-9, 2026)
- 📊 **SO Count JSON Column** - Added `so_count_json` to `ipcr_templates` table
- 🔄 **Auto-casting** - Configured Eloquent to auto-convert JSON to array
- 💾 **Migration Created** - `2026_02_05_000213_add_so_count_json_to_ipcr_templates_table.php`
- 📈 **Data Structure:**
  ```json
  {
    "strategic_objectives": 3,
    "core_functions": 3,
    "support_functions": 3
  }
  ```
- 📧 **Email Verifications Table** - New table for storing verification codes
- 🔐 **Migration Created** - `2026_02_09_192529_create_email_verifications_table.php`
- 🔑 **Schema:** `user_id`, `code` (6-digit), `created_at`, `updated_at`
- ⏱️ **Code Expiration** - 30-minute validity checked via timestamps
- 🗑️ **Auto-Cleanup** - Old codes deleted on new email or successful verification

### 🐛 Bug Fixes (February 5, 2026)
- ✅ Fixed SO numbering increment glitch (no longer jumps from SO I to SO III after removal)
- ✅ Fixed percentage display showing 100% when it should be 0%
- ✅ Fixed template preview showing "Coming Soon" message
- ✅ Fixed Save Copy duplicating templates instead of creating drafts
- ✅ Fixed sidebar toggle function compatibility across different blade files
- ✅ Removed unused `soHeaderCount` variable dependencies

## 📦 Previous Updates

### 🔐 Enhanced Password Reset Workflow (February 4, 2026)
- ✨ **3-Step Verification Process** - Email entry → Code verification → New password
- 🎯 **Modern Code Input UI** - Individual digit boxes with smooth animations and transitions
- ⌨️ **Smart Input Handling** - Auto-advance on digit entry, backspace navigation, arrow key support
- 📋 **Clipboard Integration** - Automatic paste detection and code distribution
- 🎨 **Real-time Visual States** - Empty, focused, filled, and error states with color indicators
- ⏲️ **Intelligent Resend Control** - 60-second countdown timer prevents spam
- ✅ **Verified Badge** - Shows verified email on password reset form
- 🔒 **Session-based Flow** - Ensures users complete verification before password reset
- 📱 **Mobile Optimized** - Touch-friendly input boxes with numeric keyboard support
- 🌐 **Consistent Branding** - URS logo favicon on all authentication pages

### 🔐 Password Reset Feature (NEW!)
- 📧 **Email-based Password Reset** - Users can reset forgotten passwords via email verification
- 🔢 **6-Digit Verification Code** - Secure random codes sent to user's email address
- ⏱️ **15-Minute Expiration** - Reset codes automatically expire after 15 minutes for security
- 📨 **Brevo SMTP Integration** - Production-ready email delivery through Brevo (formerly Sendinblue)
- 🎨 **Custom Email Templates** - Branded emails with URS name and customized messaging
- 🔒 **Secure Token Storage** - Hashed tokens stored in dedicated password_reset_tokens table
- ✅ **Password Validation** - Minimum 8 characters with confirmation required
- 🌐 **Accessible from Login** - "Forgot password?" link available on all login pages
- 📱 **Responsive Forms** - Mobile-friendly forgot password and reset password pages
- 🚀 **Free Tier Support** - 300 emails/day with Brevo's free plan
- 🎯 **Interactive Verification UI** - Modern 6-box code input with auto-focus and auto-submit
- 📋 **Paste Support** - Automatic code distribution across input boxes when pasting
- ⏲️ **Resend Timer** - 60-second cooldown before allowing code resend
- ✨ **Visual Feedback** - Shake animation on error, filled state indicators
- 🎨 **Favicon Support** - URS logo favicon displayed on all pages

### ✨ Image Crop & Resize Feature
- 🎬 **Interactive Crop Modal** - Draggable crop area with 1:1 aspect ratio constraint
- 🔍 **Zoom Controls** - Zoom in/out with 0.1 increments for precise image positioning
- 🔄 **Rotation Controls** - Rotate images left/right by 90° increments
- ↩️ **Reset Functionality** - Reset image to default position with one click
- 📸 **Live Preview** - Real-time preview of crop selection as you adjust
- ✅ **Crop & Upload Workflow** - Seamless from selection → crop → upload to Cloudinary
- 🎨 **Consistent UI** - Available in both Faculty Profile and Admin User Management
- 🌟 **400x400px Output** - All cropped images automatically resized to 400x400 pixels
- 📊 **Progress Tracking** - Visual progress bar during upload with success/error messaging

### Photo Management Enhancements
- 📸 **Automatic Image Compression** - All uploaded photos are automatically compressed to reduce file size
- 🖼️ **Smart Resizing** - Images larger than 1200px are automatically resized while maintaining aspect ratio
- 🎯 **Quality Optimization** - JPEG/WebP files compressed to 80% quality for perfect balance
- 👤 **Default Avatar System** - Users without photos get clean SVG silhouette avatars
- 🎨 **Styled Modals** - Consistent confirmation modals for deleting and setting profile photos
- ⚡ **Hover Controls** - Intuitive photo management with hover-triggered controls

### Admin Protection & UI Improvements
- 🔒 **Protected Administrator Account** - Main admin account (URS26-ADM00001) cannot be edited, deleted, or deactivated
- 🎨 **Compact Table Design** - User management table redesigned with reduced padding and spacing
- 📊 **No Department for Admin** - Administrator account has no department/designation assignment
- 🔐 **Lock Icon Indicator** - Admin account displays lock icon instead of edit/delete buttons
- 🔄 **Login Route Fix** - Added GET /login route that redirects to role selection

### Database Seeding Update
- ✅ **Simplified UserSeeder** - Now creates only the admin user (previously created 8 sample users)
- ✅ **Updated credentials** - Admin password changed to `password` for consistency
- ✅ **Cleaner setup** - Removed outdated sample users (Director, Deans, Faculty)
- ✅ **Aligned with schema** - Seeder now matches actual database structure

### Vite Integration & Code Refactoring
- ✅ **Extracted all inline CSS/JS** - From 9 blade templates into external files
- ✅ **Configured Vite** - Bundle all assets with hot module replacement
- ✅ **Optimized JavaScript** - Exposed functions to global scope for Vite compatibility
- ✅ **Production-ready builds** - All assets hashed for cache-busting
- ✅ **Fixed mobile menu & notifications** - Now working with bundled assets

### Files Refactored
**CSS Files Created (9):**
- `resources/css/admin_users_edit.css`
- `resources/css/admin_users_index.css`
- `resources/css/admin_users_show.css`
- `resources/css/auth_login.css`
- `resources/css/auth_login-selection.css`
- `resources/css/dashboard_admin_index.css`
- `resources/css/dashboard_faculty_index.css`
- `resources/css/dashboard_faculty_profile.css`
- `resources/css/dashboard_faculty_my-ipcrs.css`

**JS Files Created (11):**
- `resources/js/admin_users_edit.js`
- `resources/js/admin_users_index.js`
- `resources/js/admin_users_show.js`
- `resources/js/admin_users_create.js` *(NEW)*
- `resources/js/auth_login.js`
- `resources/js/auth_verify-code.js` *(NEW)*
- `resources/js/auth_reset-password.js` *(NEW)*
- `resources/js/dashboard_admin_index.js`
- `resources/js/dashboard_faculty_index.js`
- `resources/js/dashboard_faculty_profile.js`
- `resources/js/dashboard_faculty_my-ipcrs.js`

**CSS Files Created (12):**
- `resources/css/admin_users_edit.css`
- `resources/css/admin_users_index.css`
- `resources/css/admin_users_show.css`
- `resources/css/admin_users_create.css` *(NEW)*
- `resources/css/auth_login.css`
- `resources/css/auth_login-selection.css`
- `resources/css/auth_verify-code.css` *(NEW)*
- `resources/css/auth_reset-password.css` *(NEW)*
- `resources/css/dashboard_admin_index.css`
- `resources/css/dashboard_faculty_index.css`
- `resources/css/dashboard_faculty_profile.css`
- `resources/css/dashboard_faculty_my-ipcrs.css`

### Technical Improvements
- 🔧 **vite.config.js** - Configured with 23 entry points
- 🔧 **window.function** pattern - All interactive functions exposed globally
- 🔧 **@vite directive** - All blade files now use Laravel Vite plugin
- 🔧 **Build optimization** - Assets reduced from ~200KB to gzipped builds
- 🔧 **Blade templates cleaned** - Removed ~200+ lines of inline code
- 🔧 **Cloudinary Integration** - Full CDN support for user photos (upload, retrieve, delete)
- 🔧 **Session Storage** - Used for cross-page state management (email verification prompt)
- 🔧 **Eloquent Accessors** - Custom attribute accessors for profile completeness and photo URLs

### UI/UX Enhancements
- 🎨 **Split-Screen Login Design** - Modern login page with title on left, form on right
- 🌊 **Fixed Blob Animations** - Eliminated visual artifacts during scrolling
- 📱 **Mobile Scrolling Fix** - Proper overflow handling for mobile devices
- 🎯 **Responsive Layout** - Optimized for all screen sizes (mobile, tablet, desktop)
- 🎨 **Badge System** - Inline role and status badges with color coding
- 🎨 **Icon-Based Navigation** - Color-coded circular icons for quick action identification
- ⚡ **Smooth Animations** - Slide-down, fade-in, and shake animations for better UX
- 🎨 **Progress Indicators** - Horizontal bars replace circular SVG for compact display

### Developer Experience
- 🔧 **Laravel Blade Snippets** - Recommended VS Code extension for better IDE support
  - Install: "Laravel Blade Snippets" by Winnie Lin
  - Eliminates false route() parameter warnings
  - Provides autocomplete for Blade directives
  - Improves syntax highlighting

---

## 🚀 Quick Start

For detailed installation instructions, see **[INSTRUCTIONS.md](INSTRUCTIONS.md)**

### Prerequisites
- PHP >= 8.1 with GD Extension
- Composer
- Node.js & NPM (v16+)
- MySQL/MariaDB
- XAMPP (recommended for Windows)

### Quick Installation

```bash
# Clone repository
git clone https://github.com/jarlokenpaghubasan/IPCR.git
cd IPCR

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database in .env, then:
php artisan migrate
php artisan db:seed
php artisan storage:link

# Build and run
npm run build
php artisan serve
```

**Access:** http://localhost:8000  
**Login:** `admin` / `password`

### Cloudinary Setup (Required for Photo Uploads & Storage)

Cloudinary is used for uploading, storing, and serving all user profile photos via CDN.

Add these to your `.env` file:

```env
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
CLOUDINARY_URL=cloudinary://your_api_key:your_api_secret@your_cloud_name
```

**Features:**
- ☁️ Upload photos directly to Cloudinary CDN
- 🖼️ Automatic image optimization and transformation
- 📸 Photos served from Cloudinary URLs (no local storage)
- 🗑️ Delete photos from cloud on removal

See [INSTRUCTIONS.md](INSTRUCTIONS.md) for detailed Cloudinary setup guide.

---

## 🎯 Usage

### First Time Login

1. **Start the server** (if not already running):
   ```bash
   php artisan serve
   ```

2. **Open your browser** and go to: http://localhost:8000

3. **Select role:** Click on "Admin" from the login selection page

4. **Enter credentials:**
   - **Username:** `admin`
   - **Password:** `password`

5. **You're in!** You'll be redirected to the admin dashboard

### Admin Panel Features

After logging in as admin, you can:

**User Management:**
- ✅ Create new users (Faculty, Dean, Director, or additional Admins)
- ✅ Edit user information (except the main Administrator account)
- ✅ Assign multiple roles to users
- ✅ Upload profile photos for users
- ✅ Activate/Deactivate user accounts
- ✅ Delete users (except the main Administrator account)
- ✅ View detailed user information
- ✅ Filter users by department
- ✅ Search users by name, email, or username

**Dashboard:**
- View total users count
- View active/inactive users
- View users by role (Admin, Director, Dean, Faculty)
- Quick access to user management

**Protected Administrator Account:**
- The main admin account (URS26-ADM00001) is protected
- Cannot be edited, deleted, or deactivated
- Displayed with a 🔒 lock icon in the user list
- Ensures system always has at least one administrator

### Faculty Dashboard

Faculty users can:
- 📊 View performance metrics and dashboard analytics
- 📝 Access and submit IPCR forms
- 👤 Manage their profile with comprehensive controls
- 🔑 Change password securely
- 📸 Upload and manage multiple profile photos via Cloudinary
- 🖼️ Crop and resize photos before upload
- ✅ Verify email address with 6-digit codes
- 📈 Track profile completeness (8 fields monitored)
- 🔔 View notifications and system updates
- 📅 See account activity (last login, member since, photo count)
- 🏷️ View assigned roles and account status

### Creating Additional Users

1. Go to **User Management**
2. Click **"Create New User"**
3. Fill in the form:
   - Name, Email, Username
   - Password (minimum 8 characters)
   - Phone number (optional)
   - Select one or more roles
   - Select department (required for Faculty/Dean)
   - Select designation
   - Set active status
4. Click **"Create User"**
5. Employee ID will be auto-generated based on department

---

## 📁 Project Structure

```
ipcr_system/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/           # Admin panel controllers
│   │   ├── Auth/            # Authentication controllers
│   │   └── Dashboard/       # Dashboard controllers
│   ├── Models/              # Eloquent models
│   ├── Services/            # Business logic services
│   └── Middleware/          # Custom middleware
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/             # Database seeders
├── public/
│   ├── images/              # Public images (logo, etc.)
│   └── storage/             # Symlinked storage folder
├── resources/
│   ├── views/               # Blade templates
│   ├── css/                 # Stylesheets
│   └── js/                  # JavaScript files
├── routes/
│   └── web.php              # Web routes
└── storage/
    └── app/
        └── public/
            └── user_photos/ # User uploaded photos
```

---

## 🔧 Troubleshooting

---

## 🔧 Troubleshooting

For detailed troubleshooting, see **[INSTRUCTIONS.md](INSTRUCTIONS.md)**

### Quick Fixes

```bash
# Clear all caches
php artisan optimize:clear

# Rebuild assets
npm run build

# Reset database (WARNING: deletes all data)
php artisan migrate:fresh --seed

# Regenerate autoload
composer dump-autoload
```

---

## 🛠️ Development

### Running the Application

**Production Mode (Recommended for normal use):**
```bash
# Build optimized assets (only need to run once or after code changes)
npm run build

# Start Laravel development server
php artisan serve
```
Access at: http://localhost:8000

**Development Mode with Hot Reload (For developers making changes):**

Open **two separate terminal windows**:

```bash
# Terminal 1 - Vite dev server with hot reload
npm run dev

# Terminal 2 - Laravel development server
cd C:\xampp\htdocs\IPCR  # Navigate to project folder first
php artisan serve
```

**What's the difference?**
- `npm run build` - Builds assets once, optimized and minified for production
- `npm run dev` - Watches for file changes and auto-reloads browser (useful when editing CSS/JS)

### Running Tests

```bash
php artisan test
```

### Clearing Cache

If you encounter strange behavior or old data showing up:

```bash
# Clear all caches at once
php artisan optimize:clear

# Or clear individually
php artisan cache:clear      # Application cache
php artisan config:clear     # Configuration cache
php artisan route:clear      # Route cache
php artisan view:clear       # Compiled views cache
```

### Asset Management with Vite

```bash
# Development with hot module replacement (auto-refresh)
npm run dev

# Production build (optimized, minified, hashed)
npm run build

# Preview production build locally
npm run preview
```

**Vite Features:**
- ⚡ Lightning-fast hot module replacement (HMR)
- 📦 Optimized production builds with tree-shaking
- 🔄 Auto-refresh browser on file changes (with npm run dev)
- 🎯 Cache-busting with content hashes
- 📊 Build size analysis

### Database Management

**Reset database completely:**
```bash
php artisan migrate:fresh --seed
```
⚠️ **Warning:** This deletes ALL data and recreates tables with fresh seed data

**Add new migration:**
```bash
php artisan make:migration create_something_table
```

**Rollback last migration:**
```bash
php artisan migrate:rollback
```

### Code Generation

```bash
# Create new controller
php artisan make:controller NameController

# Create new model
php artisan make:model ModelName

# Create model with migration
php artisan make:model ModelName -m

# Create new seeder
php artisan make:seeder NameSeeder
```

---

## 📦 Technologies Used

- **Backend:** Laravel 11.x
- **Frontend:** Tailwind CSS v4.0, Blade Templates
- **Build Tool:** Vite 7.x with Laravel plugin
- **Database:** MySQL
- **Charts:** Chart.js
- **Authentication:** Laravel Breeze (customized)
- **Icons:** Font Awesome 6.4.0
- **Cloud Storage:** Cloudinary (image uploads & CDN)
- **Email Service:** Brevo/Sendinblue SMTP (password reset & email verification)
- **Image Processing:** Cropper.js 1.6.1 (client-side crop & resize)

---

## 👥 User Roles

| Role | Permissions |
|------|-------------|
| **Admin** | Full system access, user management, all configurations |
| **Director** | View director-level reports and dashboards |
| **Dean** | View dean-level reports and dashboards |
| **Faculty** | View personal dashboard, submit IPCRs, manage profile |

---

## 🔐 Security

- Passwords are hashed using bcrypt
- CSRF protection enabled
- XSS protection via Blade templating
- SQL injection protection via Eloquent ORM
- Role-based access control (RBAC)
- Session management with database driver

---

## 📄 License

This project is open-sourced software licensed under the MIT license.

---

## 👨‍💻 Developer

**Developed by:** Jarlo Ken E. Paghubasan  
**GitHub:** [@jarlokenpaghubasan](https://github.com/jarlokenpaghubasan)

---

## 📞 Support

For issues and questions:
1. Check the troubleshooting section above
2. Create an issue on GitHub
3. Contact the developer

---

**Happy Coding! 🚀**
