# SIPEJABAT Mobile API Blueprint (Draft v0.1)

Dokumen ini memetakan konsep SIPEJABAT Android ke backend Semar Betul saat ini, sebagai acuan sebelum implementasi API mobile.

## 1. Tujuan

- Menyamakan definisi data WILKER dan FASKES.
- Menetapkan model akses user dan role untuk mobile.
- Menentukan endpoint API minimum per fitur.
- Menentukan pola sinkronisasi dua arah Semar Betul <-> SIPEJABAT.

## 2. Kondisi Backend Saat Ini (As-Is)

Berdasarkan route dan permission yang sudah ada:

- Modul utama: dashboard, pasien, tindak lanjut, users, roles, permissions, data master.
- RBAC sudah ada (Spatie Permission) dengan middleware per route.
- Permission yang relevan tersedia:
  - pasiens.view/create/edit/delete
  - tindak-lanjut.view/verifikasi/riwayat.create/selesai
  - faskes.view, puskesmas.view, dll.
- Root web sudah redirect ke login.

Catatan: route saat ini masih dominan web (Inertia), belum dipisah kontrak API mobile.

## 3. Mapping Konsep SIPEJABAT ke Domain Backend

### 3.1 Klasifikasi Data

1. WILKER
- Definisi: pasien berdasarkan domisili wilayah kerja puskesmas.
- Kunci data: relasi pasien -> kelurahan/kecamatan -> puskesmas wilayah.
- Fungsi: IKAS, kunjungan rumah, mangkir, penyuluhan.

2. FASKES
- Definisi: pasien berdasarkan fasyankes tempat pengobatan aktif.
- Kunci data: relasi episode pengobatan pasien -> faskes aktif.
- Fungsi: pemantauan pengobatan, pengambilan obat, hasil kunjungan per faskes.

### 3.2 Akses Lembaga

1. Puskesmas
- Akses: WILKER + FASKES.

2. Fasyankes non-puskesmas
- Akses: FASKES saja.

### 3.3 Akses Role

1. Pengelola Program TB Puskesmas
- Akses: FASKES + WILKER.

2. Epidemiolog Puskesmas
- Akses: WILKER saja.

3. User Fasyankes Non-Puskesmas
- Akses: FASKES saja.

## 4. Matriks Akses Fitur

| Fitur | Pengelola TB Puskesmas | Epidemiolog Puskesmas | User Fasyankes Non-Puskesmas |
|---|---|---|---|
| FASKES - Pengambilan Obat | Ya | Tidak | Ya |
| FASKES - Hasil Kunjungan | Ya | Tidak | Ya |
| WILKER - Kunjungan IKAS | Ya | Ya | Tidak |
| WILKER - Kunjungan Rumah | Ya | Ya | Tidak |
| WILKER - Kunjungan Mangkir | Ya | Ya | Tidak |
| WILKER - Hasil Kunjungan | Ya | Ya | Tidak |
| WILKER - Penyuluhan | Ya | Ya | Tidak |

## 5. Gap Analisis (To-Be)

Agar konsep SIPEJABAT berjalan penuh, backend perlu tambahan:

1. Kontrak API mobile terpisah
- Prefix: /api/mobile/v1
- Auth berbasis token (Sanctum personal access token).

2. Penandaan domain data
- Penentuan tegas context data: WILKER atau FASKES di query layer.
- Scope filter wajib berdasarkan user login (role + unit + kewenangan).

3. Alur status pengobatan FASKES
- Status pengambilan obat yang dibutuhkan:
  - terrealisasi
  - terrealisasi_obat_terakhir
  - tidak_datang
  - meninggal
  - pindah_fasyankes

4. Alur kunjungan WILKER
- IKAS, rumah (bulan ke-2, ke-5, akhir), mangkir, penyuluhan.
- Perlu penyimpanan lokasi (latitude, longitude, sumber gps/maps).

5. Sinkronisasi dua arah
- Mekanisme teknis perlu ditetapkan (lihat bagian 8).

## 6. Rekomendasi Struktur Endpoint Mobile (Draft)

### 6.1 Auth

- POST /api/mobile/v1/auth/login
- POST /api/mobile/v1/auth/logout
- GET /api/mobile/v1/auth/me

### 6.2 Dashboard ringkas mobile

- GET /api/mobile/v1/dashboard/summary

### 6.3 FASKES

1. Pengambilan obat
- GET /api/mobile/v1/faskes/medication-pickups?date=YYYY-MM-DD
- PATCH /api/mobile/v1/faskes/medication-pickups/{id}/status
  - payload status:
    - terrealisasi (+ next_pickup_date wajib)
    - terrealisasi_obat_terakhir
    - tidak_datang
    - meninggal (+ death_date wajib)
    - pindah_fasyankes (+ target_faskes_id atau is_outside_city)

2. Hasil kunjungan
- GET /api/mobile/v1/faskes/visit-results

### 6.4 WILKER

1. Kunjungan IKAS
- GET /api/mobile/v1/wilker/ikas
- POST /api/mobile/v1/wilker/ikas/{pasien_id}/result

2. Kunjungan rumah
- GET /api/mobile/v1/wilker/home-visits
- POST /api/mobile/v1/wilker/home-visits/{pasien_id}/result

3. Kunjungan mangkir
- GET /api/mobile/v1/wilker/mangkir
- POST /api/mobile/v1/wilker/mangkir/{pasien_id}/result

4. Hasil kunjungan
- GET /api/mobile/v1/wilker/visit-results

5. Penyuluhan
- GET /api/mobile/v1/wilker/counseling/targets
- POST /api/mobile/v1/wilker/counseling

## 7. Rekomendasi Permission Baru (Draft)

Agar lebih granular untuk mobile:

- mobile.auth.login
- mobile.dashboard.view

FASKES
- mobile.faskes.medication-pickups.view
- mobile.faskes.medication-pickups.update
- mobile.faskes.visit-results.view

WILKER
- mobile.wilker.ikas.view
- mobile.wilker.ikas.create
- mobile.wilker.home-visits.view
- mobile.wilker.home-visits.create
- mobile.wilker.mangkir.view
- mobile.wilker.mangkir.create
- mobile.wilker.visit-results.view
- mobile.wilker.counseling.view
- mobile.wilker.counseling.create

## 8. Arsitektur Sinkronisasi Dua Arah (Draft)

Target: data Semar Betul dan SIPEJABAT selalu konsisten.

### 8.1 Opsi Sinkronisasi

1. API Pull + Push terjadwal
- Scheduler ambil delta data berdasarkan updated_at/last_sync_at.
- Cocok untuk tahap awal.

2. Event/Webhook
- Perubahan penting memicu webhook antar sistem.
- Lebih real-time, perlu reliability layer.

3. Hybrid (direkomendasikan)
- Event untuk near real-time.
- Scheduler sebagai safety net/reconciliation.

### 8.2 Aturan Minimal Sinkronisasi

- Semua record punya external_id dan source_system.
- Idempotency key untuk operasi tulis.
- Conflict rule jelas:
  - last-write-wins untuk field non-kritis, atau
  - source-of-truth specific per domain.
- Audit log sinkronisasi wajib (request, response, status, retrial).

## 9. Standar Response API (Draft)

Contoh sukses:

{
  "success": true,
  "message": "OK",
  "data": { },
  "meta": { }
}

Contoh gagal validasi:

{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["error message"]
  }
}

## 10. Prioritas Implementasi Bertahap

Tahap 1
- Auth token + profile me
- Read-only list FASKES & WILKER
- Filter dasar dan pagination

Tahap 2
- Update status pengambilan obat
- Input hasil kunjungan mangkir

Tahap 3
- IKAS, kunjungan rumah, penyuluhan + geo point
- Sinkronisasi dua arah (hybrid)

Tahap 4
- Hardening: observability, retry policy, reconciliation dashboard

## 11. Konfirmasi Strategis

Dokumen ini menyatakan konsep SIPEJABAT sejalan dengan backend Semar Betul saat ini pada level arsitektur, namun membutuhkan:

- pemisahan API mobile,
- penegasan business scope WILKER/FASKES,
- perluasan permission dan endpoint,
- serta mekanisme sinkronisasi dua arah yang terstandar.
