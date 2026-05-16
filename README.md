# Rotan Semar CMS

Sistem manajemen data pasien TBC, tindak lanjut, pengambilan obat, laporan Excel, kegiatan penyuluhan, dan master data wilayah/fasilitas.

## Fitur Utama

- Autentikasi dan otorisasi (Fortify + Spatie Permission).
- CRUD data master: User, Role, Permission, OPD, Faskes, Puskesmas, Kecamatan, Kelurahan, Pekerjaan, Jenis Kebutuhan, Jenis Penanganan.
- Manajemen pasien (list, detail, tambah, ubah, hapus).
- Import pasien bulk via Excel (admin).
- Tindak lanjut kebutuhan pasien dan upload foto.
- Pengambilan obat.
- Laporan export multi-sheet Excel.
- Kegiatan penyuluhan dan upload dokumentasi.

## Stack

### Backend
- PHP ^8.3
- Laravel Framework ^13.7
- Laravel Fortify ^1.34
- Laravel Sanctum ^4.0
- Spatie Laravel Permission ^7.4

### Frontend
- Blade (Laravel View)
- Tailwind CSS ^4.0.0

## Prasyarat

- PHP 8.3+ dengan extension umum Laravel (`bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`)
- Composer 2.7+
- Node.js 20+
- npm 10+
- MySQL/MariaDB atau SQLite

Opsional production:
- Redis (cache/queue)
- Supervisor (worker process)

## Setup (Development)

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan optimize:clear
```

Rekomendasi `.env`:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `FORCE_HTTPS=true`
- `SESSION_SECURE_COOKIE=true`