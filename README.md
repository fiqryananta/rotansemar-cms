# Rotan Semar CMS

Siistem untuk manajemen data pasien TBC, tindak lanjut, laporan Excel, kegiatan penyuluhan, dan master data wilayah/fasilitas.

## 1. Requirement Specification

### 1.1 Functional Scope (CMS)
- Autentikasi user (Fortify + email verification + 2FA).
- Otorisasi berbasis Role dan Permission (Spatie Permission).
- CRUD master data: User, Role, Permission, OPD, Faskes, Puskesmas, Kecamatan, Kelurahan, Jenis Penanganan, Jenis Kebutuhan, Pekerjaan.
- Manajemen pasien (list, detail, tambah, edit, hapus).
- Import pasien bulk via Excel (khusus admin).
- Tindak lanjut kebutuhan pasien + upload foto.
- Pengambilan obat.
- Laporan export multi-sheet Excel.
- Kegiatan penyuluhan + upload dokumentasi.

### 1.2 Non-Functional Requirement
- Security: RBAC, CSRF, session web, upload validation, security headers.
- Performance: pagination default, query filtering, import dibatasi ukuran file + throttle + chunk processing.
- Maintainability: lint, format check, type check, test command tersedia.
- Deployability: bisa dijalankan mode development dan production build.

## 2. Tech Stack dan Versi

### 2.1 Backend
- PHP: ^8.3
- Laravel Framework: ^13.7
- Inertia Laravel: ^3.0
- Laravel Fortify: ^1.34
- Laravel Sanctum: ^4.0
- Spatie Laravel Permission: ^7.4
- Maatwebsite Excel: ^3.1
- Ziggy: ^2.6

### 2.2 Frontend
- React: ^19.2.0
- TypeScript: ^5.7.2
- Vite: ^8.0.0
- Tailwind CSS: ^4.0.0
- Inertia React: ^3.0.0
- Lucide React: ^0.475.0

### 2.3 Tooling
- Composer scripts: setup, dev, test, lint, ci:check.
- ESLint + Prettier + TypeScript type check.

## 3. Software Requirement

Wajib terpasang:
- PHP 8.3+ beserta extension umum Laravel:
  - bcmath
  - ctype
  - fileinfo
  - json
  - mbstring
  - openssl
  - pdo
  - tokenizer
  - xml
- Composer 2.7+
- Node.js 20+
- npm 10+
- Database server (MySQL/MariaDB) atau SQLite untuk lokal

Opsional (disarankan production):
- Redis (cache/queue)
- Supervisor untuk worker queue

## 4. Panduan Instalasi (Development)

### 4.1 Clone dan Install Dependency
```bash
composer install
npm install
```

### 4.2 Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

Isi konfigurasi penting di file `.env`:
- `APP_NAME`
- `APP_URL`
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `SESSION_DRIVER=database`

### 4.3 Inisialisasi Database
```bash
php artisan migrate
```

Jika ada seeder role/permission pada project Anda, jalankan juga:
```bash
php artisan db:seed
```

### 4.4 Build Asset dan Jalankan Aplikasi
```bash
npm run build
```

Alternatif development paralel (server + queue + vite):
```bash
composer run dev
```

### 4.5 Storage Link (untuk file upload)
```bash
php artisan storage:link
```

## 5. Panduan Deploy (Production)

### 5.1 Environment Minimum
Set di `.env` production:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://domain.com`
- `FORCE_HTTPS=true`
- `LOG_LEVEL=warning` (atau `error`)
- `SESSION_SECURE_COOKIE=true`
- `SESSION_SAME_SITE=lax`

### 5.2 Build dan Optimasi
```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5.3 Jalankan Queue Worker
Contoh:
```bash
php artisan queue:work --tries=1 --timeout=120
```

## 6. Command Quality Check

```bash
# backend lint/style
composer run lint:check

# frontend lint + format + typecheck
npm run lint:check
npm run format:check
npm run types:check

# test
composer test
```

## 7. Troubleshooting Singkat

- Build gagal karena route generator tidak ditemukan:
  - Jalankan ulang `npm run build` setelah sinkron perubahan route/fitur auth.
- 403 pada fitur tertentu:
  - Cek role dan permission user aktif.