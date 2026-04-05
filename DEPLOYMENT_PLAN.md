# Deployment Plan

This document separates setup and deployment instructions from the main README.

## 1. Install Required Tools (Windows)
Run PowerShell as Administrator:

```powershell
winget install --id Git.Git -e
winget install --id ApacheFriends.Xampp -e
winget install --id Composer.Composer -e
winget install --id OpenJS.NodeJS.LTS -e
```

Verify installation:

```powershell
git --version
php --version
composer --version
node --version
npm --version
```

## 2. Install Laravel Installer (Requested)
This project is cloned from GitHub, but installing the Laravel installer is still useful for creating future Laravel apps.

```powershell
composer global require laravel/installer
laravel --version
```

If `laravel` is not recognized, add Composer global bin to PATH and restart terminal:

```powershell
$env:Path += ";$env:USERPROFILE\AppData\Roaming\Composer\vendor\bin"
```

## 3. Pull Project From GitHub

```powershell
cd C:\xampp\htdocs
git clone https://github.com/jarloken02-png/ipcr_sys.git ipcr_system_v10
cd ipcr_system_v10
```

## 4. Install Project Dependencies

```powershell
composer install
npm install
```

## 5. Configure Environment
Create local environment file:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Update `.env` with local database and service credentials:

- APP_NAME, APP_ENV, APP_DEBUG, APP_URL
- DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
- MAIL_MAILER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_ENCRYPTION, MAIL_FROM_ADDRESS, MAIL_FROM_NAME
- AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION, AWS_BUCKET, AWS_ENDPOINT, AWS_URL, AWS_USE_PATH_STYLE_ENDPOINT
- BACKUP_DIRECTORY, MEDIA_URL_TTL_MINUTES

Recommended local database values:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ipcr_system_v10
DB_USERNAME=root
DB_PASSWORD=
```

## 6. Create and Seed Database
Create database in local MySQL:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS ipcr_system_v10 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Run migrations, seeders, and storage link:

```powershell
php artisan migrate --seed --force
php artisan storage:link
```

## 7. Run the Application Locally

```powershell
npm run build
php artisan serve
php artisan about
```

Open in browser:
- http://127.0.0.1:8000

Default seeded accounts (password is `password`):
- admin
- dean
- director
- faculty
- faculty2

## 8. Create Cloudflare R2 Bucket (for media storage)
1. Go to Cloudflare Dashboard and open R2.
2. Create a bucket for media files.
3. Create R2 API credentials (access key and secret key).
4. Copy your account R2 endpoint and bucket name.
5. Set these in `.env`:

```env
AWS_ACCESS_KEY_ID=your_r2_access_key
AWS_SECRET_ACCESS_KEY=your_r2_secret_key
AWS_DEFAULT_REGION=auto
AWS_BUCKET=your_r2_bucket_name
AWS_ENDPOINT=https://<accountid>.r2.cloudflarestorage.com
AWS_URL=
AWS_USE_PATH_STYLE_ENDPOINT=false
BACKUP_DIRECTORY=backups
MEDIA_URL_TTL_MINUTES=30
```

6. Save `.env` and clear config cache if needed:

```powershell
php artisan config:clear
```

## 9. Create Brevo Account (for email and verification flows)
1. Go to https://www.brevo.com and create an account.
2. Verify sender identity/domain in Brevo.
3. Create SMTP key in Brevo Settings.
4. Set mail values in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_brevo_username
MAIL_PASSWORD=your_brevo_smtp_key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_verified_sender@example.com
MAIL_FROM_NAME="University of Rizal System Binangonan"
```

5. Test mail path with app flows (forgot password and email verification).

## 10. Deploy to Railway (Flow We Used)
This is the same deployment pattern that worked for us in this project session.

### 10.1 Prepare repository
- Push latest code to GitHub.
- Keep `nixpacks.toml` in repository root.
- This project already has build/start commands in `nixpacks.toml`.

### 10.2 Install and login Railway CLI

```powershell
npm install -g @railway/cli
railway login
```

### 10.3 Create Railway project and services
1. In Railway dashboard, create a new project.
2. Add a MySQL service/plugin.
3. Add a web service from GitHub repo:
   - https://github.com/jarloken02-png/ipcr_sys.git

### 10.4 Configure environment variables in Railway
Set app variables in your web service:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-railway-domain.up.railway.app
APP_KEY=base64:generated_app_key

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_brevo_username
MAIL_PASSWORD=your_brevo_smtp_key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_verified_sender@example.com
MAIL_FROM_NAME="University of Rizal System Binangonan"

AWS_ACCESS_KEY_ID=your_r2_access_key
AWS_SECRET_ACCESS_KEY=your_r2_secret_key
AWS_DEFAULT_REGION=auto
AWS_BUCKET=your_r2_bucket_name
AWS_ENDPOINT=https://<accountid>.r2.cloudflarestorage.com
AWS_URL=
AWS_USE_PATH_STYLE_ENDPOINT=false
BACKUP_DIRECTORY=backups
MEDIA_URL_TTL_MINUTES=30
```

Notes:
- `APP_KEY` can be generated locally with `php artisan key:generate --show`.
- Keep secrets in Railway Variables, not in Git.

### 10.5 Deploy and monitor
Trigger deployment from Railway dashboard (or by pushing to linked branch).

Monitor build logs:

```powershell
railway logs --build <build-id> --latest
```

### 10.6 Run migrations/seeding in production container
Use in-container execution for production commands:

```powershell
railway link
railway ssh -s <web-service-name> -e production "php artisan migrate --force"
railway ssh -s <web-service-name> -e production "php artisan db:seed --force"
railway ssh -s <web-service-name> -e production "php artisan storage:link --force"
```

### 10.7 Validate deployment
- Open Railway domain.
- Test login page and dashboard redirection.
- Verify DB-backed features (users, reports, backups).
- Check file upload and email flows (Cloudflare R2 and Brevo).

## 11. Deployment Notes Learned in This Session
- `railway run` executes commands locally with Railway env variables injected.
- For true inside-container commands, use `railway ssh -s <service> -e production <command>`.
- If runtime tables like sessions/cache are dropped during a restore, rerun migrations in production to recover quickly.
- Use `railway logs --build <build-id> --latest` to inspect build failures and confirm fixes.
