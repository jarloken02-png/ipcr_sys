# 🔐 API Keys & Credentials

⚠️ **IMPORTANT:** This file contains sensitive credentials. Never commit this to public repositories!

---

## ☁️ Cloudinary API (Photo Storage)

Add these to your `.env` file for cloud-based image storage:

```env
CLOUDINARY_CLOUD_NAME=dntjrz3mi
CLOUDINARY_API_KEY=666187645511746
CLOUDINARY_API_SECRET=0b0aCaMlUAvBoiBU_5srXLVD16o
CLOUDINARY_URL=cloudinary://666187645511746:0b0aCaMlUAvBoiBU_5srXLVD16o@dntjrz3mi
```

**Account Details:**
- Dashboard: https://cloudinary.com/console
- Cloud Name: `dntjrz3mi`
- API Key: `666187645511746`
- API Secret: `0b0aCaMlUAvBoiBU_5srXLVD16o`

---

## 🗄️ Database Configuration

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ipcr_system_v4
DB_USERNAME=root
DB_PASSWORD=
```

**For Production:**
- Update `DB_PASSWORD` with a strong password
- Consider using environment-specific database names

---

## 📧 Brevo Email Service (Password Reset Feature)

**Currently Configured for Production:**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_brevo_login@smtp-brevo.com
MAIL_PASSWORD=your_brevo_smtp_key_here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your_verified_email@gmail.com"
MAIL_FROM_NAME="University of Rizal System Binangonan"
```

**Brevo Account Details:**
- Dashboard: https://www.brevo.com
- Login: (Your Brevo login email)
- Verified Sender: (Your verified email for URSIPCR)
- Free Tier: 300 emails/day
- SMTP Server: smtp-relay.brevo.com

**How to Get Your SMTP Key:**
1. Log in to Brevo dashboard
2. Go to SMTP & API section
3. Generate a new SMTP key
4. Add verified sender email in Senders section
5. Update .env file with your credentials

**Password Reset Flow:**
1. User clicks "Forgot password?" on login page
2. System generates 6-digit code (valid 15 minutes)
3. Code sent via email through Brevo
4. User enters code + new password
5. Password successfully reset

**Alternative Services:**
- [Mailtrap](https://mailtrap.io) - For testing emails (500/month free)
- [Gmail SMTP](https://mail.google.com) - 500 emails/day (requires App Password)
- [SendGrid](https://sendgrid.com) - 100 emails/day free
- [Mailgun](https://mailgun.com) - Alternative option

---

## 🔑 Application Key

Generate a unique application key (automatically done via `php artisan key:generate`):

```env
APP_KEY=base64:generated_key_will_appear_here
```

---

## 🌐 GitHub Repository Links

- **Main Repository:** https://github.com/jarlokenpaghubasan/IPCR.git
- **Backup Repository:** https://github.com/markchristianacerdenpbtsc-dev/IPCR-sa-account-ni-den.git

---

## 👤 Default Admin Credentials

After running `php artisan db:seed`:

```
Username: admin
Password: password
Email: admin@ipcr.system
Employee ID: URS26-ADM00001
```

⚠️ **Change the password immediately in production!**

---

## 📝 Notes

- All credentials should be kept in `.env` file (already in `.gitignore`)
- Never share API secrets publicly
- Use different credentials for development and production
- Rotate API keys periodically for security

---

**Last Updated:** February 4, 2026
