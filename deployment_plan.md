# Deployment Plan (Local Computer - Windows + XAMPP)

This guide is a simple command-first checklist to run the IPCR system locally.

## 1) Install Required Tools

Run these in PowerShell as Administrator.

| Step | Command (1 line) | Explanation |
|---|---|---|
| 1 | winget install --id ApacheFriends.Xampp -e | Installs XAMPP (Apache, MySQL, PHP stack). |
| 2 | winget install --id Composer.Composer -e | Installs Composer for Laravel dependencies. |
| 3 | winget install --id OpenJS.NodeJS.LTS -e | Installs Node.js and npm for frontend build tools. |

## 2) Get the Project

| Step | Command (1 line) | Explanation |
|---|---|---|
| 4 | cd C:\xampp\htdocs | Moves into XAMPP web root. |
| 5 | git clone https://github.com/jarlokenpaghubasan/IPCR.git ipcr_system_v10 | Downloads the project source code. |
| 6 | cd ipcr_system_v10 | Opens the project folder. |

## 3) Install Dependencies

| Step | Command (1 line) | Explanation |
|---|---|---|
| 7 | composer install | Installs PHP/Laravel dependencies. |
| 8 | npm install | Installs JavaScript dependencies. |

## 4) Configure Environment

| Step | Command (1 line) | Explanation |
|---|---|---|
| 9 | Copy-Item .env.example .env | Creates your local environment file. |
| 10 | notepad .env | Opens .env so you can set DB, MAIL, and CLOUDINARY values. |
| 11 | php artisan key:generate | Generates APP_KEY in .env. |

Minimum .env values to confirm:
- DB_DATABASE=ipcr_system_v10
- DB_USERNAME=root
- DB_PASSWORD=
- MAIL_MAILER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_ENCRYPTION, MAIL_FROM_ADDRESS, MAIL_FROM_NAME
- CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY, CLOUDINARY_API_SECRET, CLOUDINARY_URL

## 5) Create Database and Load Data

| Step | Command (1 line) | Explanation |
|---|---|---|
| 12 | C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS ipcr_system_v10 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" | Creates the database in local MySQL. |
| 13 | php artisan migrate --seed --force | Creates all tables and inserts default data. |
| 14 | php artisan storage:link | Creates public storage symlink for uploaded files. |

## 6) Build Frontend and Run

| Step | Command (1 line) | Explanation |
|---|---|---|
| 15 | npm run build | Builds production-ready CSS/JS assets. |
| 16 | php artisan serve | Starts the local Laravel server. |
| 17 | php artisan about | Confirms Laravel, DB, mail driver, and storage link status. |

Open in browser after Step 16:
- http://127.0.0.1:8000

Default seeded login (if unchanged):
- Username: admin
- Password: password

## Quick Troubleshooting

| Problem | Command (1 line) | Explanation |
|---|---|---|
| PHP or Composer not found | where php; where composer | Checks if executables are available in PATH. |
| Node or npm not found | where node; where npm | Confirms Node.js tools are installed and in PATH. |
| Start fresh database | php artisan migrate:fresh --seed --force | Rebuilds all tables and reseeds data. |
