# 🦷 OrthoBrain

> A modern orthodontic case management platform built with Laravel 13 and Livewire 4, featuring AI-powered photo quality control, treatment planning, and prescription workflows.

![Laravel](https://img.shields.io/badge/Laravel-13-red?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php)
![Livewire](https://img.shields.io/badge/Livewire-4-4E56A6?style=for-the-badge)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4.0-38B2AC?style=for-the-badge&logo=tailwind-css)
![Pest](https://img.shields.io/badge/Tested%20with-Pest-7B68EE?style=for-the-badge)

---

## 📌 Overview

OrthoBrain is a comprehensive orthodontic case management platform for doctors and administrators.

### Doctor Features
- Register and verify account
- Join practices
- Create treatment cases using a multi-step wizard
- Upload photographs, X-rays, and impressions
- Generate AI-powered smile plans
- Submit prescriptions

### Admin Features
- Approve or reject doctor registrations
- Review orthodontic cases
- Manage products, scanners, and practices
- Generate reports and PDFs

---

## 🚀 Tech Stack

| Layer | Technology |
|------|------|
| Backend | PHP 8.3+, Laravel 13 |
| Frontend | Livewire 4, Blade, Alpine.js |
| UI Template | Vuexy |
| Build Tool | Vite 8 |
| Styling | Tailwind CSS 4 |
| Database | MySQL 8.0+ |
| PDF | barryvdh/laravel-dompdf |
| Testing | Pest 4, PHPUnit 12 |
| AI | Google Gemini 2.0 Flash, Ollama |
| Mail | Mailtrap |

---

## 📋 System Requirements

- PHP >= 8.3
- Composer >= 2.6
- Node.js >= 18
- npm >= 9
- MySQL >= 8.0
- Git
- Optional: Ollama

### Required PHP Extensions

- mbstring
- openssl
- pdo_mysql
- tokenizer
- xml
- ctype
- json
- bcmath
- fileinfo
- gd

---

## 📁 Project Structure

```text
OrthoBrain/
├── app/
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── docs/
├── public/
├── resources/
│   ├── views/
│   ├── js/
│   └── css/
├── routes/
├── storage/
├── tests/
├── .env.example
├── artisan
├── composer.json
├── package.json
└── vite.config.js
```

---

## ⚙️ Installation

```bash
# Clone repository
git clone <repository-url> OrthoBrain
cd OrthoBrain

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Create database
mysql -u root -p -e "CREATE DATABASE ob1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations and seeders
php artisan migrate --seed

# Create storage symlink
php artisan storage:link

# Build assets
npm run build
```

### Quick Setup

```bash
composer setup
```

---

## 🔧 Environment Configuration

### Application

```env
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
```

### Database

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ob1
DB_USERNAME=root
DB_PASSWORD=manager
```

### Session / Cache / Queue

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
```

### Mailtrap SMTP

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@orthobrain.local"
MAIL_FROM_NAME="${APP_NAME}"
```

### Seeded Credentials

```env
SUPER_ADMIN_EMAIL=admin@orthobrain.local
SUPER_ADMIN_PASSWORD=Password@1

TEST_DOCTOR_EMAIL=doctor@orthobrain.local
TEST_DOCTOR_PASSWORD=Password@1
```

### Branding

```env
ADMIN_BRAND_NAME=orthobrain
ADMIN_BRAND_TAGLINE="Orthodontics for Your Dental Practice"
```

### Google reCAPTCHA v3

```env
RECAPTCHA_SITE_KEY=
RECAPTCHA_SECRET_KEY=
RECAPTCHA_MIN_SCORE=0.5
RECAPTCHA_TIMEOUT=4
```

### AI Configuration

```env
AI_PROVIDER_CHAIN=gemini,ollama,canned

GEMINI_API_KEY=
AI_GEMINI_MODEL=gemini-2.0-flash

AI_OLLAMA_URL=http://localhost:11434
AI_OLLAMA_QC_MODEL=moondream
AI_OLLAMA_SMILE_MODEL=llava:7b
AI_OLLAMA_SMILE_TIMEOUT_SECONDS=90

AI_IMAGE_EDIT_PROVIDER_CHAIN=gemini,canned
AI_IMAGE_EDIT_MODEL=gemini-2.5-flash-image
AI_IMAGE_EDIT_TIMEOUT_MS=30000
```

---

## 🗄️ Database Commands

```bash
# Run migrations
php artisan migrate

# Fresh migration + seed
php artisan migrate:fresh --seed

# Rollback
php artisan migrate:rollback

# Check status
php artisan migrate:status
```

---

## 🌱 Seeder Execution Order

1. LocationMasterSeeder
2. ManageTypesSeeder
3. SuperAdminSeeder
4. MasterOptionsSeeder
5. PracticesSeeder
6. DoctorSeeder
7. CaseDemoSeeder
8. CaseDashboardSeeder

```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=SuperAdminSeeder
```

---

## ▶️ Running the Project

### Recommended Development Command

```bash
composer dev
```

This starts:
- Laravel server
- Queue listener
- Laravel Pail
- Vite HMR server

### Manual Startup

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev

# Terminal 3
php artisan queue:listen
```

Open: `http://localhost:8000`

---

## 🔐 Default Credentials

| Role | Email | Password |
|------|------|------|
| Super Admin | admin@orthobrain.local | Password@1 |
| Test Doctor | doctor@orthobrain.local | Password@1 |

---

## 👥 User Roles

### Doctor
- Create and manage cases
- Upload media
- Submit treatment prescriptions
- Track case status

### Admin
- Review cases
- Approve doctors
- Manage master data
- Control workflow

---

## 📦 Case Workflow

```text
DRAFT
  ↓
SUBMITTED
  ↓
IN_REVIEW
  ↓
APPROVED / REJECTED
```

---

## 🧠 AI Features

- Photo quality control
- Smile plan generation
- Before/after smile preview
- Automatic fallback to Ollama
- Canned mock responses for offline development

---

## 📄 PDF Reports

Uses `barryvdh/laravel-dompdf` to generate printable treatment reports.

---

## 🔒 Security Features

- Email verification
- OTP validation
- Password reset
- Role-based access control
- reCAPTCHA v3
- Encrypted sessions

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Or
composer test
```

---

## 🛠️ Common Artisan Commands

```bash
# Clear all caches
php artisan optimize:clear

# Individual cache commands
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Development tools
php artisan route:list
php artisan tinker
php artisan pail
php artisan queue:work --once
php artisan storage:link
```

---

## 🐞 Troubleshooting

### Clear Everything

```bash
php artisan optimize:clear
```

### Rebuild Frontend

```bash
npm run build
```

### Recreate Storage Symlink

```bash
php artisan storage:link
```

### After Git Pull

Restart your development server:

```bash
php artisan serve
```

---

## 🚀 Production Deployment

```bash
composer install --optimize-autoloader --no-dev
npm run build
php artisan migrate --force
php artisan optimize
```

Set:
- `APP_ENV=production`
- `APP_DEBUG=false`

---

## 📚 Documentation

Additional documentation:
- `docs/`
- `Docs/architecture/`
- `CLAUDE.md`

---

## 🤝 Contributing

1. Create a feature branch
2. Commit your changes
3. Run tests
4. Open a pull request

---

## 📜 License

Proprietary software developed for orthodontic practice management.

---

## 👨‍💻 Author

**Kamlesh Kasambe**

---

## ⭐ Support

If you find this project useful, please star the repository.
