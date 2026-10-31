<?php

namespace App\Exports;

use App\Imports\PasienImport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Template import pasien dengan susunan kolom yang sama seperti file contoh
 * SemarBetul. Header-nya diambil dari PasienImport::COLUMNS supaya template
 * unduhan dan pembaca file tidak pernah berbeda urutan/nama kolom.
 */
class PasienImportTemplateExport implements FromArray, WithTitle
{
    public function title(): string
    {
        return 'Import Pasien';
    }

    public function array(): array
    {
        return [
            PasienImport::COLUMNS,
            $this->exampleRow(),
        ];
    }

    /**
     * Satu baris contoh. Nilainya sengaja memakai data master contoh yang ada di
     * seeder supaya bisa langsung dicoba tanpa membuat master data baru.
     */
    private function exampleRow(): array
    {
        return [
            'Puskesmas Bulu Lor',           // puskesmas wilker
            '',                             // pasien (ID lama, tidak dipakai)
            '3374010101010001',             // nik
            'Budi Santoso',                 // name
            30,                             // age -> dipakai menurunkan birth_date
            'L',                            // gender: L / P
            'RSUD K.R.M.T Wongsonegoro',    // faskes
            '',                             // status (tidak dipakai)
            'Semarang',                     // city
            'Semarang Utara',               // regency -> kecamatan
            'Bulu Lor',                     // village -> kelurahan
            'Jl. Contoh No. 1',             // address
            '',                             // telp (tidak dipakai)
            '',                             // hasil_diagnosis (tidak dipakai)
            '2026-05-10',                   // date_start -> treatment_start_date
            '2026-05-10',                   // tgl_kunjungan -> visit_date
            'Tidak',                        // status_hamil
            'Tidak ada',                    // penyakit
            'Lainnya',                      // pekerjaan
            'Contoh tempat kerja',          // tempat_kerja
            'Budi Santoso',                 // nama_kepala_keluarga
            'Lainnya',                      // pekerjaan_kepala_keluarga
            '< 1.000.000',                  // total_pendapatan_keluarga_per_bulan
            '',                             // pola_asuh
            'Kawin',                        // status_pernikahan (tidak dipakai)
            'Nikah',                        // status_pernikahan_orang_tua
            3,                              // jumlah_anggota_keluarga (tidak dipakai)
            'Ya',                           // kepemilikan_jkn
            'Tidak',                        // perilaku_merokok
            'Tidak',                        // anggota_keluarga_merokok
            'Tidak',                        // mendapatkan_bantuan
            'Lengkap',                      // status_imunisasi
            'Normal',                       // status_gizi
            'Pribadi',                      // status_rumah
            45,                             // luas_rumah
            'BAIK',                         // tipe_rumah
            'Ada',                          // langit-langit
            'Permanen',                     // dinding
            'Baik',                         // lantai
            'Ada',                          // jendela_kamar_tidur
            'Ada',                          // jendela_ruang_keluarga
            'Baik',                         // ventilasi
            'Ada',                          // lubang_asap_dapur
            'Baik',                         // pencahayaan
            'Ada',                          // sarana_air_bersih
            'Ada',                          // jamban
            'Ada',                          // sarana_pembuangan_air_limbah
            'Ada',                          // sarana_pembuangan_sampah
            'Petugas',                      // sampah
            'Setiap hari dibuka',           // membuka_jendela_kamar_tidur (tidak dipakai)
            'Setiap hari dibuka',           // membuka_jendela_ruang_keluarga
            'Setiap hari',                  // membersihkan_rumah
            'Setiap hari ke jamban',        // membuang_tinja
            'Dibuang ke tempat sampah',     // membuang_sampah
            'Setiap hari CTPS',             // kebiasaan_ctps
            'Tidak',                        // memiliki_hewan_ternak
            '',                             // kandang_hewan
            'Bersedia',                     // bantuan -> willing_to_help
            'Permakanan',                   // jenis -> jenis kebutuhan pasien
        ];
    }
}
