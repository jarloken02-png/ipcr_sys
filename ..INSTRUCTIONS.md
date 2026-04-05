# 📋 IPCR System - Complete Installation Guide

This guide will help you set up the IPCR System on any computer from scratch.

---

## 📌 Table of Contents

1. [Prerequisites](#-prerequisites)
2. [Step-by-Step Installation](#-step-by-step-installation)
3. [Cloudinary Setup](#-cloudinary-setup-for-photo-uploads)
4. [Quick Setup Guide](#-quick-setup-guide-tldr)
5. [Running the Application](#-running-the-application)
6. [Troubleshooting](#-troubleshooting)

---

## 📦 Prerequisites

Make sure you have the following installed on your computer:

| Software | Version | Download Link |
|----------|---------|---------------|
| PHP | >= 8.1 | [Download PHP](https://www.php.net/downloads) |
| Composer | Latest | [Download Composer](https://getcomposer.org/download/) |
| Node.js & NPM | v16+ | [Download Node.js](https://nodejs.org/) |
| MySQL/MariaDB | Any | Included with XAMPP |
| XAMPP (Windows) | Latest | [Download XAMPP](https://www.apachefriends.org/) |

### ⚠️ Important: Enable PHP GD Extension

The system requires the PHP GD extension for image processing. If using XAMPP:

1. Open `C:\xampp\php\php.ini` in a text editor (**Run as Administrator**)
2. Find `;extension=gd` and remove the semicolon to make it `extension=gd`
3. Save the file
4. Restart Apache in XAMPP Control Panel
5. Verify GD is enabled:
   ```bash
   php -m | findstr gd
   ```

---

## 🚀 Step-by-Step Installation

### Step 1️⃣ Clone the Repository

```bash
git clone https://github.com/jarlokenpaghubasan/IPCR.git
cd IPCR
```

**Or download as ZIP:**
- Download from GitHub → Extract to `C:\xampp\htdocs\IPCR`
- Open terminal and navigate to the project folder

---

### Step 2️⃣ Install PHP Dependencies

```bash
composer install
```

**Troubleshooting:**
- Ensure Composer is installed: `composer -v`
- Check PHP version: `php -v` (must be 8.1+)

---

### Step 3️⃣-b Turbo.js Integration (SPA Navigation)

The project uses **Turbo.js** (`@hotwired/turbo`) for seamless single-page navigation and instant transitions.

- **Turbo.js is automatically installed** with the `npm install` command above (see `package.json`).
- No extra installation is needed.
- Turbo is imported and started in `resources/js/admin_layout.js`.
- All admin navigation and dynamic content swaps use Turbo.js for a fast SPA experience.

**If you encounter issues with navigation or page transitions:**
- Ensure `npm install` completed successfully.
- Check that `@hotwired/turbo` appears in your `node_modules` and `package.json`.
- Rebuild assets with `npm run build` if needed.
- For XAMPP: `C:\xampp\php\php.exe composer.phar install`
---

### Step 3️⃣ Install NPM Dependencies

```bash
npm install
```

**Troubleshooting:**
- Verify Node.js: `node -v` and `npm -v`
- Clear cache if issues: `npm cache clean --force`

---

### Step 4️⃣ Environment Configuration

**Copy the environment file:**

```powershell
# Windows (PowerShell)
Copy-Item .env.example .env

# Linux/Mac
cp .env.example .env
```

**Edit `.env` file with your database settings:**

```env
APP_NAME="IPCR System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=Asia/Hong_Kong
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ipcr_system
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
```

**Generate Application Key:**

```bash
php artisan key:generate
```

---

### Step 5️⃣ Database Setup

**Option A: Using phpMyAdmin (Recommended for XAMPP)**

1. Start Apache and MySQL in XAMPP Control Panel
2. Open http://localhost/phpmyadmin
3. Click "New" in the left sidebar
4. Database name: `ipcr_system`
5. Collation: `utf8mb4_unicode_ci`
6. Click "Create"

**Option B: Using Command Line**

```bash
# Windows XAMPP
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE ipcr_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Linux/Mac
mysql -u root -e "CREATE DATABASE ipcr_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

**Run Migrations:**

```bash
php artisan migrate
```

**Note:** This will create all necessary tables including:
- users, departments, designations, user_roles
- sessions, cache, password_reset_tokens
- user_photos, ipcr_templates, ipcr_submissions, ipcr_saved_copies
- **Template Analytics:** `so_count_json` column in `ipcr_templates` for tracking SO counts

---

### Step 6️⃣ Seed Database

```bash
php artisan db:seed
```

This creates:
- ✅ 3 Departments (COA, COB, CCS)
- ✅ 4 Designations (Professor, Associate Professor, Assistant Professor, Instructor)
- ✅ 1 Admin User

**Default Admin Credentials:**
| Field | Value |
|-------|-------|
| Username | `admin` |
| Password | `password` |
| Employee ID | `URS26-ADM00001` |
| Email | `admin@ipcr.system` |

---

### Step 7️⃣ Storage Setup

```bash
php artisan storage:link
```

**Note:** On Windows, run PowerShell/CMD as **Administrator**

---

### Step 8️⃣ Create Images Folder (Optional)

```powershell
# Windows
New-Item -Path "public/images" -ItemType Directory -Force

# Linux/Mac
mkdir -p public/images
```

Place your logo as `public/images/urs_logo.jpg` (optional)

---

### Step 9️⃣ Password Reset Configuration (Email Setup)

The system includes a password reset feature with 6-digit verification codes. To enable email sending:

**Brevo (Sendinblue) Configuration:**

1. **Create free Brevo account:** https://www.brevo.com/
2. **Get SMTP credentials:**
   - Go to Settings → SMTP & API
   - Create new SMTP key
   - Copy the credentials

3. **Update `.env` file:**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_brevo_email@example.com
MAIL_PASSWORD=your_brevo_smtp_key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@ursipcr.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Password Reset Features:**
- 📧 Users receive 6-digit codes via email
- ⏱️ Codes expire after 15 minutes
- 🎯 Interactive UI with individual digit input boxes
- 📋 Paste support for copying codes
- ⏲️ 60-second cooldown for resending codes
- ✅ Email verification before password reset

**Free Tier Limits:**
- 300 emails per day
- Perfect for development and small deployments

**Testing:**
```bash
php artisan tinker
>>> Illuminate\Support\Facades\Mail::raw('Test email', function($msg) { $msg->to('test@example.com')->subject('Test'); });
```

---

### Step 9️⃣ Build Frontend Assets

```bash
npm run build
```

⚠️ **This step is mandatory before running the application!**

**What gets built:**
- 23 optimized asset bundles (CSS + JS)
- Minified and cache-busted files
- Production-ready builds with gzip compression

**Asset breakdown:**
- Auth pages: login, verify-code, reset-password, login-selection (6 files)
- Dashboard: faculty index/profile/my-ipcrs, admin index (8 files)
- Admin users: index, show, edit, create (8 files)
- Additional: 1 manifest.json file

---

### Step 🔟 Start the Server

```bash
php artisan serve
```

Access the application at: **http://localhost:8000**

---

## ☁️ Cloudinary Setup (For Photo Uploads)

The system uses **Cloudinary** for cloud-based image storage. Follow these steps to configure it:

### 1. Create a Cloudinary Account

1. Go to [https://cloudinary.com](https://cloudinary.com)
2. Click **"Sign Up For Free"**
3. Fill in your details and verify your email
4. After login, you'll be taken to the Dashboard

### 2. Get Your API Credentials

From your Cloudinary Dashboard, locate the **API Environment variable** section. You'll need:

- **Cloud Name** (e.g., `dntjrz3mi`)
- **API Key** (e.g., `666187645511746`)
- **API Secret** (e.g., `0b0aCaMlUAvBoiBU_5srXLVD16o`)

### 3. Add Cloudinary Credentials to `.env`

Open your `.env` file and add these lines at the bottom:

```env
# Cloudinary Configuration
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
CLOUDINARY_URL=cloudinary://your_api_key:your_api_secret@your_cloud_name
```

**Example with actual values:**
```env
CLOUDINARY_CLOUD_NAME=dntjrz3mi
CLOUDINARY_API_KEY=666187645511746
CLOUDINARY_API_SECRET=0b0aCaMlUAvBoiBU_5srXLVD16o
CLOUDINARY_URL=cloudinary://666187645511746:0b0aCaMlUAvBoiBU_5srXLVD16o@dntjrz3mi
```

### 4. Verify Configuration

After adding the credentials, clear the config cache:

```bash
php artisan config:clear
```

Now photo uploads will work with cloud storage!

### 5. Using the Shared Cloudinary Account (Optional)

If you want to use the project's shared Cloudinary account, add these exact values to your `.env`:

```env
CLOUDINARY_CLOUD_NAME=dntjrz3mi
CLOUDINARY_API_KEY=666187645511746
CLOUDINARY_API_SECRET=0b0aCaMlUAvBoiBU_5srXLVD16o
CLOUDINARY_URL=cloudinary://666187645511746:0b0aCaMlUAvBoiBU_5srXLVD16o@dntjrz3mi
```

---

## ⚡ Quick Setup Guide (TL;DR)

For experienced developers, here's the condensed version:

```bash
# Clone and enter project
git clone https://github.com/jarlokenpaghubasan/IPCR.git
cd IPCR

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Edit .env - set database credentials and Cloudinary API keys

# Database (create ipcr_system database first in phpMyAdmin)
php artisan migrate
php artisan db:seed
php artisan storage:link

# Build assets and run
npm run build
php artisan serve

# Access: http://localhost:8000
# Login: admin / password
```

---

## 🖥️ Running the Application

### Production Mode (Normal Use)

```bash
npm run build          # Build assets once
php artisan serve      # Start server
```

### Development Mode (For Coding)

Open **two terminal windows**:

```bash
# Terminal 1 - Vite dev server (auto-refresh on changes)
npm run dev

# Terminal 2 - Laravel server
php artisan serve
```

### Access Points

| URL | Description |
|-----|-------------|
| http://localhost:8000 | Main application |
| http://localhost/phpmyadmin | Database management (XAMPP) |

---

## � Dashboard Features & Template Management

### Faculty Dashboard Analytics

The faculty dashboard provides real-time performance tracking:

**Performance Metrics:**
- **Strategic Objectives Progress:** Shows accomplished/total (e.g., 0/3, 1/3, 2/3)
- **Core Functions Progress:** Tracks completion per SO
- **Support Functions Progress:** Monitors support activities
- **Smart Percentages:** Auto-calculates (0/3 = 0%, 1/3 = 33%, 2/3 = 67%, 3/3 = 100%)
- **N/A Display:** Shows "N/A" when no template is active

**How it works:**
1. Create an IPCR template with SO entries
2. Click "Save as Template"
3. Set template as "Active" (radio button)
4. Dashboard automatically syncs and displays counts
5. Metrics update in real-time

### Creating IPCR Templates

Faculty users can build structured templates:

**Template Builder Features:**
- ✅ **Section Headers:** Add Strategic Objectives, Core Functions, Support Functions
- ✅ **SO Headers:** Auto-numbered with Roman numerals (I, II, III, IV...)
- ✅ **Smart Numbering:** Resets when starting new sections
- ✅ **Unlimited Support:** Goes beyond XV (XVI, XVII, XVIII...)
- ✅ **Remove Last Row:** Delete sections/SOs with proper counter updates
- ✅ **Add Data Rows:** Insert rows for MFO, targets, measures, etc.

**Workflow:**
1. Click "Create IPCR" in My IPCRs page
2. Add section header (e.g., Strategic Objectives)
3. Click "Add SO" to add numbered objectives
4. Fill in MFO, targets, measures
5. Repeat for other sections
6. Click "Save as Template"
7. Set as Active to enable dashboard tracking

**Save Copy vs Save as Template:**
- **Save as Template:** Creates reusable structure (appears in Templates section)
- **Save Copy:** Creates editable draft (appears in Saved Copy section)
- Click "View" on template → "Save Copy" button creates draft

### Template Management

**Actions available:**
- **Use:** Load template into IPCR document for editing
- **View:** Preview template in modal with Save Copy option
- **Delete:** Remove template permanently
- **Set Active (radio):** Enable dashboard metrics tracking
- **Save Copy:** Create editable draft in Saved Copy section

**JSON Storage:**
When you save a template, the system automatically:
1. Counts SOs in each section (Strategic Objectives, Core Functions, Support Functions)
2. Stores counts as JSON in database
3. Updates dashboard when template is set as active
4. Calculates percentages based on accomplished vs total

---
## 👨‍🏫 Dean Review & Calibration (NEW!)

### For Dean Users Only

Deans have two additional sidebar sections for reviewing submissions:

#### 📋 Faculty IPCRs (Indigo Section)
- **Purpose:** Review IPCR submissions from faculty members in your department
- **Filtering:** Only shows faculty from your assigned department (e.g., CSS dean sees CSS faculty only)
- **Information Displayed:**
  - Faculty name and employee ID
  - IPCR title
  - School year and semester
  - Submission date
  - Current status
- **Actions:** Click "View" to open submission in read-only preview modal
- **Access:** Automatically appears for users with dean role

#### 🎓 Dean Calibration (Amber Section)
- **Purpose:** Cross-calibration by viewing other deans' IPCR submissions
- **Scope:** Shows IPCR submissions from all other deans across all departments
- **Information Displayed:**
  - Dean name and employee ID
  - Department badge (CSS, COA, COB, etc.)
  - IPCR title
  - School year and semester
  - Submission date
  - Current status
- **Actions:** Click "View" to open submission in read-only preview modal
- **Use Case:** Enables deans to review and calibrate their performance against peers

### How It Works

1. **Automatic Loading:** Both sections load automatically when you open "My IPCRs" page
2. **Read-Only View:** Clicking "View" opens the template preview modal
   - All cells are non-editable
   - No update or save buttons
   - Color-tinted background (indigo for faculty, amber for deans)
3. **Real-Time Data:** Lists update automatically based on new submissions
4. **Role Protection:** Only accessible if you have the dean role assigned

### Technical Details

- **API Endpoints:**
  - `GET /dean/review/faculty-submissions` - List faculty submissions
  - `GET /dean/review/faculty-submissions/{id}` - View specific faculty submission
  - `GET /dean/review/dean-submissions` - List other deans' submissions
  - `GET /dean/review/dean-submissions/{id}` - View specific dean submission
- **Security:** All endpoints protected by `auth` and `role:dean` middleware
- **Database Queries:** Filtered by department_id and user roles for security

---
## �🔧 Troubleshooting

### Common Issues and Solutions

| Issue | Solution |
|-------|----------|
| **GD extension not found** | Enable in `php.ini`: change `;extension=gd` to `extension=gd`, restart Apache |
| **Assets not loading** | Run `npm run build` |
| **Changes not appearing** | Clear browser cache (Ctrl+Shift+R or Cmd+Shift+R) |
| **Vite build fails** | Delete `node_modules`, run `npm install`, then `npm run build` |
| **Port 8000 in use** | Use `php artisan serve --port=8001` |
| **Storage link error** | Run as Administrator |
| **Database connection error** | Check MySQL is running, verify `.env` credentials |
| **Cloudinary upload fails** | Check API credentials in `.env`, run `php artisan config:clear` |

### Clearing All Caches

```bash
php artisan optimize:clear
```

### Reset Database Completely

```bash
php artisan migrate:fresh --seed
```

⚠️ **Warning:** This deletes ALL data!

### Rebuild All Assets

If styles or JavaScript aren't working:

```bash
# Clean build
npm run build

# Or rebuild from scratch
rm -rf node_modules
rm package-lock.json
npm install
npm run build
```

### Check Build Output

Verify all 23 assets were built:

```bash
npm run build
```

Look for:
- ✅ 12 CSS files
- ✅ 11 JS files
- ✅ 1 manifest.json

### Check GD Extension

```bash
# Windows
php -m | findstr gd

# Linux/Mac
php -m | grep gd
```

---

## 📞 Need Help?

1. Check the troubleshooting section above
2. Create an issue on GitHub
3. Contact the developer

---

**Happy Coding! 🚀**
