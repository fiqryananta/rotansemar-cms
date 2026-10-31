<?php

namespace App\Imports;

use App\Models\Faskes;
use App\Models\JenisKebutuhan;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Pasien;
use App\Models\Pekerjaan;
use App\Models\Puskesmas;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Import pasien mengikuti template SemarBetul (59 kolom).
 *
 * Satu baris = satu pasien. Kolom yang tidak punya padanan di tabel pasiens
 * (pasien/ID lama, city, telp, hasil_diagnosis, status, status_pernikahan,
 * jumlah_anggota_keluarga, membuka_jendela_kamar_tidur) sengaja diabaikan;
 * daftarnya ada di IGNORED_COLUMNS supaya jelas apa yang tidak masuk.
 *
 * Beberapa nilai diturunkan karena bentuknya berbeda dengan tabel:
 * - age -> birth_date (tanggal hari import dikurangi umur, karena template tidak
 *   menyimpan tanggal lahir). Kalau file kebetulan punya kolom birth_date, nilai
 *   itu yang dipakai.
 * - gender L/P -> laki-laki/perempuan.
 * - Ya/Tidak, Bersedia, Ada -> boolean.
 * - Nama faskes/puskesmas/kecamatan/kelurahan/pekerjaan/jenis kebutuhan dicari
 *   dulu di master data (tanpa membedakan huruf besar/kecil); kalau belum ada,
 *   dibuat baru dari nama di file.
 */
class PasienImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithChunkReading, WithBatchInserts
{
    /**
     * Header kolom template, urut sama persis dengan file contoh SemarBetul.
     * Dipakai juga oleh PasienImportTemplateExport supaya template unduhan tidak
     * bisa menyimpang dari yang dibaca di sini.
     */
    public const COLUMNS = [
        'puskesmas wilker',
        'pasien',
        'nik',
        'name',
        'age',
        'gender',
        'faskes',
        'status',
        'city',
        'regency',
        'village',
        'address',
        'telp',
        'hasil_diagnosis',
        'date_start',
        'tgl_kunjungan',
        'status_hamil',
        'penyakit',
        'pekerjaan',
        'tempat_kerja',
        'nama_kepala_keluarga',
        'pekerjaan_kepala_keluarga',
        'total_pendapatan_keluarga_per_bulan',
        'pola_asuh',
        'status_pernikahan',
        'status_pernikahan_orang_tua',
        'jumlah_anggota_keluarga',
        'kepemilikan_jkn',
        'perilaku_merokok',
        'anggota_keluarga_merokok',
        'mendapatkan_bantuan',
        'status_imunisasi',
        'status_gizi',
        'status_rumah',
        'luas_rumah',
        'tipe_rumah',
        'langit-langit',
        'dinding',
        'lantai',
        'jendela_kamar_tidur',
        'jendela_ruang_keluarga',
        'ventilasi',
        'lubang_asap_dapur',
        'pencahayaan',
        'sarana_air_bersih',
        'jamban',
        'sarana_pembuangan_air_limbah',
        'sarana_pembuangan_sampah',
        'sampah',
        'membuka_jendela_kamar_tidur',
        'membuka_jendela_ruang_keluarga',
        'membersihkan_rumah',
        'membuang_tinja',
        'membuang_sampah',
        'kebiasaan_ctps',
        'memiliki_hewan_ternak',
        'kandang_hewan',
        'bantuan',
        'jenis',
    ];

    /**
     * Kolom yang wajib ada di file. Dua kolom penentu tanggal lahir (age atau
     * birth_date) diperiksa terpisah karena cukup salah satunya.
     */
    public const REQUIRED_COLUMNS = [
        'puskesmas_wilker',
        'nik',
        'name',
        'gender',
        'faskes',
        'regency',
        'village',
        'address',
    ];

    /**
     * Pesan validasi berbahasa Indonesia. Tanpa ini Laravel memakai pesan
     * bawaan berbahasa Inggris ("The nik field must be 16 digits."), padahal
     * daftar baris yang dilewati ditampilkan apa adanya ke pengguna.
     */
    private const MESSAGES = [
        'required' => ':attribute wajib diisi.',
        'string' => ':attribute harus berupa teks.',
        'date' => ':attribute harus berupa tanggal yang valid.',
        'digits' => ':attribute harus terdiri dari :digits digit angka.',
        'exists' => ':attribute yang dipilih tidak valid.',
        'in' => ':attribute yang dipilih tidak valid.',
        'max' => ':attribute tidak boleh lebih dari :max karakter.',
    ];

    /** Label kolom untuk placeholder :attribute pada MESSAGES di atas. */
    private const ATTRIBUTES = [
        'name' => 'Nama Pasien',
        'nik' => 'NIK',
        'birth_date' => 'Tanggal Lahir',
        'gender' => 'Jenis Kelamin',
        'address' => 'Alamat',
        'puskesmas_wilker' => 'Puskesmas Wilker',
        'faskes' => 'Faskes',
        'regency' => 'Kecamatan',
        'village' => 'Kelurahan',
        'faskes_id' => 'Faskes',
        'puskesmas_id' => 'Puskesmas',
        'kecamatan_id' => 'Kecamatan',
        'kelurahan_id' => 'Kelurahan',
        'treatment_start_date' => 'Tanggal Mulai Pengobatan',
        'visit_date' => 'Tanggal Kunjungan',
        'family_income_range' => 'Total Pendapatan Keluarga per Bulan',
        'parent_marital_status' => 'Status Pernikahan Orang Tua',
        'immunization_status' => 'Status Imunisasi',
        'nutritional_status' => 'Status Gizi',
        'workplace_name' => 'Tempat Kerja',
        'family_head_name' => 'Nama Kepala Keluarga',
        'parenting_pattern' => 'Pola Asuh',
        'house_status' => 'Status Rumah',
        'house_area' => 'Luas Rumah',
        'house_type' => 'Tipe Rumah',
        'home_ceiling' => 'Langit-langit',
        'home_wall' => 'Dinding',
        'home_floor' => 'Lantai',
        'home_bedroom_window' => 'Jendela Kamar Tidur',
        'home_family_room_window' => 'Jendela Ruang Keluarga',
        'home_ventilation' => 'Ventilasi',
        'home_kitchen_smoke_hole' => 'Lubang Asap Dapur',
        'home_lighting' => 'Pencahayaan',
        'home_open_family_room_window' => 'Membuka Jendela Ruang Keluarga',
        'home_clean_house_habit' => 'Membersihkan Rumah',
        'sanitation_clean_water' => 'Sarana Air Bersih',
        'sanitation_toilet' => 'Jamban',
        'sanitation_wastewater_disposal' => 'Sarana Pembuangan Air Limbah',
        'sanitation_garbage_water_disposal' => 'Sarana Pembuangan Sampah',
        'sanitation_trash' => 'Sampah',
        'sanitation_feces_disposal' => 'Membuang Tinja',
        'sanitation_throw_trash_habit' => 'Membuang Sampah',
        'sanitation_handwashing_habit' => 'Kebiasaan CTPS',
    ];

    /** Kolom template yang tidak disimpan karena tidak ada padanannya. */
    public const IGNORED_COLUMNS = [
        'pasien',
        'status',
        'city',
        'telp',
        'hasil_diagnosis',
        'status_pernikahan',
        'jumlah_anggota_keluarga',
        'membuka_jendela_kamar_tidur',
    ];

    private int $inserted = 0;
    private int $updated = 0;
    private int $skipped = 0;

    /** @var array<int, string> */
    private array $errors = [];

    /** Pesan yang membatalkan seluruh file (mis. kolom wajib tidak ada). */
    private ?string $fatal = null;

    private bool $headingsChecked = false;

    /** @var array<string, int> Cache id master data per nama, supaya tidak query berulang. */
    private array $masterCache = [];

    public function collection(Collection $rows): void
    {
        if (! $this->headingsChecked) {
            $this->headingsChecked = true;

            if ($rows->isEmpty()) {
                $this->fatal = 'File tidak berisi baris data. Gunakan template import yang disediakan.';

                return;
            }

            $missing = $this->missingColumns($rows->first()->keys()->all());

            if ($missing !== []) {
                $this->fatal = 'Kolom wajib belum ada di file: '
                    . implode(', ', array_map($this->displayColumn(...), $missing))
                    . '. Unduh ulang template import agar susunan kolomnya sesuai.';

                return;
            }
        }

        foreach ($rows as $index => $row) {
            $line = $index + 2;

            try {
                $this->importRow($row, $line);
            } catch (\Throwable $exception) {
                $this->skipped++;
                $this->errors[] = "Baris {$line}: " . $exception->getMessage();
            }
        }
    }

    public function summary(): array
    {
        return [
            'inserted' => $this->inserted,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'total' => $this->inserted + $this->updated + $this->skipped,
        ];
    }

    /** @return array<int, string> */
    public function errors(): array
    {
        return $this->errors;
    }

    public function fatalError(): ?string
    {
        return $this->fatal;
    }

    public function chunkSize(): int
    {
        return 250;
    }

    public function batchSize(): int
    {
        return 250;
    }

    /**
     * @param  array<int, string>  $headings
     * @return array<int, string>
     */
    private function missingColumns(array $headings): array
    {
        $missing = array_values(array_diff(self::REQUIRED_COLUMNS, $headings));

        if (! in_array('age', $headings, true) && ! in_array('birth_date', $headings, true)) {
            $missing[] = 'age';
        }

        return $missing;
    }

    /**
     * Kembalikan nama kolom seperti yang tertulis di file ("puskesmas wilker",
     * "langit-langit"), bukan bentuk yang sudah dinormalisasi oleh pembaca
     * Excel, supaya pengguna bisa langsung mencarinya di template.
     */
    private function displayColumn(string $column): string
    {
        foreach (self::COLUMNS as $header) {
            if (Str::slug($header, '_') === $column) {
                return $header;
            }
        }

        return str_replace('_', ' ', $column);
    }

    private function importRow(Collection $row, int $line): void
    {
        $jenisKebutuhan = $this->asNullableString($row['jenis'] ?? null);

        // Validasi nilai dasar dulu, sebelum master data dibuat. Baris yang
        // datanya tidak lengkap tidak boleh meninggalkan master data baru.
        $basic = Validator::make([
            'name' => $this->asString($row['name'] ?? null),
            'nik' => $this->normalizeNik($row['nik'] ?? null),
            'birth_date' => $this->resolveBirthDate($row),
            'gender' => $this->resolveGender($row['gender'] ?? null),
            'address' => $this->asString($row['address'] ?? null),
            'puskesmas_wilker' => $this->asString($row['puskesmas_wilker'] ?? null),
            'faskes' => $this->asString($row['faskes'] ?? null),
            'regency' => $this->asString($row['regency'] ?? null),
            'village' => $this->asString($row['village'] ?? null),
        ], [
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'digits:16'],
            'birth_date' => ['required', 'date'],
            'gender' => ['required', Rule::in(['laki-laki', 'perempuan'])],
            'address' => ['required', 'string'],
            'puskesmas_wilker' => ['required', 'string', 'max:255'],
            'faskes' => ['required', 'string', 'max:255'],
            'regency' => ['required', 'string', 'max:255'],
            'village' => ['required', 'string', 'max:255'],
        ], self::MESSAGES, self::ATTRIBUTES);

        if ($basic->fails()) {
            $this->skipped++;
            $this->errors[] = "Baris {$line}: " . implode('; ', $basic->errors()->all());

            return;
        }

        // Master data: dicocokkan menurut nama, dibuat kalau belum ada.
        $kecamatanId = $this->resolveId(Kecamatan::class, $basic->validated()['regency']);
        $kelurahanId = $this->resolveId(Kelurahan::class, $basic->validated()['village'], ['kecamatan_id' => $kecamatanId]);
        $jenisKebutuhanId = $jenisKebutuhan === null
            ? null
            : $this->resolveId(JenisKebutuhan::class, $jenisKebutuhan);

        $payload = array_merge($basic->validated(), [
            'faskes_id' => $this->resolveId(Faskes::class, $basic->validated()['faskes']),
            'puskesmas_id' => $this->resolveId(Puskesmas::class, $basic->validated()['puskesmas_wilker']),
            'kecamatan_id' => $kecamatanId,
            'kelurahan_id' => $kelurahanId,

            'coordinates' => null,
            'treatment_start_date' => $this->parseDate($row['date_start'] ?? null),
            'visit_date' => $this->parseDate($row['tgl_kunjungan'] ?? null),

            'pregnancy_status' => $this->toBoolean($row['status_hamil'] ?? null, false),
            'comorbid_status' => $this->hasDisease($row['penyakit'] ?? null),

            'pekerjaan_id' => $this->resolveOptionalId(Pekerjaan::class, $row['pekerjaan'] ?? null),
            'workplace_name' => $this->asNullableString($row['tempat_kerja'] ?? null),
            'family_head_name' => $this->asNullableString($row['nama_kepala_keluarga'] ?? null),
            'family_head_pekerjaan_id' => $this->resolveOptionalId(Pekerjaan::class, $row['pekerjaan_kepala_keluarga'] ?? null),

            'family_income_range' => $this->resolveIncomeRange($row['total_pendapatan_keluarga_per_bulan'] ?? null),
            'parenting_pattern' => $this->asNullableString($row['pola_asuh'] ?? null),
            'parent_marital_status' => $this->resolveParentMaritalStatus($row['status_pernikahan_orang_tua'] ?? null),

            'jkn_ownership' => $this->toBoolean($row['kepemilikan_jkn'] ?? null, false),
            'smoking_behavior' => $this->toBoolean($row['perilaku_merokok'] ?? null, false),
            'family_smoking_status' => $this->toBoolean($row['anggota_keluarga_merokok'] ?? null, false),
            'ever_received_assistance' => $this->toBoolean($row['mendapatkan_bantuan'] ?? null, false),
            'willing_to_help' => $this->toBoolean($row['bantuan'] ?? null, false),
            'respondent' => false,
            'immunization_status' => $this->resolveImmunizationStatus($row['status_imunisasi'] ?? null),
            'nutritional_status' => $this->resolveNutritionalStatus($row['status_gizi'] ?? null),

            'house_status' => $this->asNullableString($row['status_rumah'] ?? null),
            'house_area' => $this->asNullableString($row['luas_rumah'] ?? null),
            'house_type' => $this->asNullableString($row['tipe_rumah'] ?? null),
            'home_ceiling' => $this->asNullableString($row['langit_langit'] ?? null),
            'home_wall' => $this->asNullableString($row['dinding'] ?? null),
            'home_floor' => $this->asNullableString($row['lantai'] ?? null),
            'home_bedroom_window' => $this->asNullableString($row['jendela_kamar_tidur'] ?? null),
            'home_family_room_window' => $this->asNullableString($row['jendela_ruang_keluarga'] ?? null),
            'home_ventilation' => $this->asNullableString($row['ventilasi'] ?? null),
            'home_kitchen_smoke_hole' => $this->asNullableString($row['lubang_asap_dapur'] ?? null),
            'home_lighting' => $this->asNullableString($row['pencahayaan'] ?? null),
            'home_open_family_room_window' => $this->asNullableString($row['membuka_jendela_ruang_keluarga'] ?? null),
            'home_clean_house_habit' => $this->asNullableString($row['membersihkan_rumah'] ?? null),

            'sanitation_clean_water' => $this->asNullableString($row['sarana_air_bersih'] ?? null),
            'sanitation_toilet' => $this->asNullableString($row['jamban'] ?? null),
            'sanitation_wastewater_disposal' => $this->asNullableString($row['sarana_pembuangan_air_limbah'] ?? null),
            'sanitation_garbage_water_disposal' => $this->asNullableString($row['sarana_pembuangan_sampah'] ?? null),
            'sanitation_trash' => $this->asNullableString($row['sampah'] ?? null),
            'sanitation_feces_disposal' => $this->asNullableString($row['membuang_tinja'] ?? null),
            'sanitation_throw_trash_habit' => $this->asNullableString($row['membuang_sampah'] ?? null),
            'sanitation_handwashing_habit' => $this->asNullableString($row['kebiasaan_ctps'] ?? null),

            'has_livestock' => $this->toNullableBoolean($row['memiliki_hewan_ternak'] ?? null),
            'has_animal_cage' => $this->toNullableBoolean($row['kandang_hewan'] ?? null),
        ]);

        unset($payload['puskesmas_wilker'], $payload['faskes'], $payload['regency'], $payload['village']);
        $validator = Validator::make($payload, [
            'faskes_id' => ['required', 'exists:faskes,id'],
            'puskesmas_id' => ['required', 'exists:puskesmas,id'],
            'kecamatan_id' => ['required', 'exists:kecamatans,id'],
            'kelurahan_id' => ['required', 'exists:kelurahans,id'],
            'treatment_start_date' => ['nullable', 'date'],
            'visit_date' => ['nullable', 'date'],
            'family_income_range' => ['nullable', Rule::in(['<1000000', '>1000000'])],
            'parent_marital_status' => ['nullable', Rule::in(['menikah', 'cerai'])],
            'immunization_status' => ['nullable', Rule::in(['lengkap', 'tidak-lengkap'])],
            'nutritional_status' => ['nullable', Rule::in(['normal', 'tidak-normal'])],
            'workplace_name' => ['nullable', 'string', 'max:255'],
            'family_head_name' => ['nullable', 'string', 'max:255'],
            'parenting_pattern' => ['nullable', 'string', 'max:255'],
            'house_status' => ['nullable', 'string', 'max:255'],
            'house_area' => ['nullable', 'string', 'max:255'],
            'house_type' => ['nullable', 'string', 'max:255'],
            'home_ceiling' => ['nullable', 'string', 'max:255'],
            'home_wall' => ['nullable', 'string', 'max:255'],
            'home_floor' => ['nullable', 'string', 'max:255'],
            'home_bedroom_window' => ['nullable', 'string', 'max:255'],
            'home_family_room_window' => ['nullable', 'string', 'max:255'],
            'home_ventilation' => ['nullable', 'string', 'max:255'],
            'home_kitchen_smoke_hole' => ['nullable', 'string', 'max:255'],
            'home_lighting' => ['nullable', 'string', 'max:255'],
            'home_open_family_room_window' => ['nullable', 'string', 'max:255'],
            'home_clean_house_habit' => ['nullable', 'string', 'max:255'],
            'sanitation_clean_water' => ['nullable', 'string', 'max:255'],
            'sanitation_toilet' => ['nullable', 'string', 'max:255'],
            'sanitation_wastewater_disposal' => ['nullable', 'string', 'max:255'],
            'sanitation_garbage_water_disposal' => ['nullable', 'string', 'max:255'],
            'sanitation_trash' => ['nullable', 'string', 'max:255'],
            'sanitation_feces_disposal' => ['nullable', 'string', 'max:255'],
            'sanitation_throw_trash_habit' => ['nullable', 'string', 'max:255'],
            'sanitation_handwashing_habit' => ['nullable', 'string', 'max:255'],
        ], self::MESSAGES, self::ATTRIBUTES);

        if ($validator->fails()) {
            $this->skipped++;
            $this->errors[] = "Baris {$line}: " . implode('; ', $validator->errors()->all());

            return;
        }

        // $payload, bukan validated(): validated() hanya mengembalikan kunci yang
        // punya aturan, sedangkan boolean dan id master data di sini sudah
        // dibentuk oleh kode sendiri dan tidak perlu aturan tambahan.
        $data = $payload;

        $existing = Pasien::query()->where('nik', $data['nik'])->first();

        if ($existing) {
            $existing->update($data);
            $this->updated++;
            $pasien = $existing;
        } else {
            $pasien = Pasien::query()->create($data);
            $this->inserted++;
        }

        if ($jenisKebutuhanId !== null) {
            $pasien->jenisKebutuhans()->syncWithoutDetaching([$jenisKebutuhanId]);
        }
    }

    /**
     * Id master data untuk satu nama. Nama dicocokkan tanpa membedakan huruf
     * besar/kecil; kalau belum ada, baris master dibuat dari nama di file.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  array<string, mixed>  $scope  Kolom tambahan penentu baris, mis. kecamatan_id untuk kelurahan
     */
    private function resolveId(string $model, string $name, array $scope = []): int
    {
        $name = trim($name);
        $cacheKey = $model . '|' . json_encode($scope) . '|' . mb_strtolower($name);

        if (isset($this->masterCache[$cacheKey])) {
            return $this->masterCache[$cacheKey];
        }

        $query = $model::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)]);

        foreach ($scope as $column => $value) {
            $query->where($column, $value);
        }

        $record = $query->first() ?? $model::query()->create(['name' => $name] + $scope);

        return $this->masterCache[$cacheKey] = (int) $record->getKey();
    }

    /**
     * Sama seperti resolveId(), tetapi nilai kosong tetap kosong dan tidak
     * membuat baris master baru.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $model
     */
    private function resolveOptionalId(string $model, mixed $value): ?int
    {
        $name = $this->asNullableString($value);

        return $name === null ? null : $this->resolveId($model, $name);
    }

    /**
     * Tanggal lahir dari kolom birth_date kalau ada, kalau tidak diturunkan dari
     * umur (tanggal hari import dikurangi umur). Template SemarBetul hanya
     * menyimpan umur, jadi hasilnya perkiraan.
     */
    private function resolveBirthDate(Collection $row): ?string
    {
        $explicit = $this->parseDate($row['birth_date'] ?? null);

        if ($explicit !== null) {
            return $explicit;
        }

        $age = $this->asNullableInt($row['age'] ?? null);

        if ($age === null || $age < 0 || $age > 150) {
            return null;
        }

        return Carbon::now()->subYears($age)->toDateString();
    }

    private function normalizeNik(mixed $value): string
    {
        // Nilai dari Excel kadang masih membawa apostrof penanda teks.
        return preg_replace('/\D/', '', (string) $value) ?? '';
    }

    private function resolveGender(mixed $value): ?string
    {
        $gender = mb_strtolower($this->asString($value));

        return match (true) {
            $gender === '' => null,
            in_array($gender, ['l', 'lk', 'laki-laki', 'laki laki', 'pria', 'male', 'm'], true) => 'laki-laki',
            in_array($gender, ['p', 'pr', 'perempuan', 'wanita', 'female', 'f'], true) => 'perempuan',
            default => null,
        };
    }

    /**
     * Penyakit penyerta: "Tidak ada" berarti tidak ada komorbid, selain itu ada.
     */
    private function hasDisease(mixed $value): bool
    {
        $text = mb_strtolower($this->asString($value));

        if ($text === '' || in_array($text, ['tidak', 'tidak ada', 'tidak ada penyakit', '-'], true)) {
            return false;
        }

        return true;
    }

    private function resolveParentMaritalStatus(mixed $value): ?string
    {
        $text = mb_strtolower($this->asString($value));

        return match (true) {
            $text === '' => null,
            str_contains($text, 'cerai') || in_array($text, ['janda', 'duda'], true) => 'cerai',
            str_contains($text, 'kawin') || str_contains($text, 'nikah') => 'menikah',
            default => null,
        };
    }

    private function resolveImmunizationStatus(mixed $value): ?string
    {
        $text = mb_strtolower($this->asString($value));

        return match (true) {
            $text === '' => null,
            str_contains($text, 'tidak') || str_contains($text, 'belum') => 'tidak-lengkap',
            str_contains($text, 'lengkap') => 'lengkap',
            default => null,
        };
    }

    /**
     * Tabel hanya mengenal normal dan tidak-normal, sedangkan template memakai
     * istilah gizi tersendiri (Underweight, Obesitas, dst).
     */
    private function resolveNutritionalStatus(mixed $value): ?string
    {
        $text = mb_strtolower($this->asString($value));

        if ($text === '') {
            return null;
        }

        return $text === 'normal' || str_contains($text, 'baik') ? 'normal' : 'tidak-normal';
    }

    /**
     * Pendapatan keluarga dipetakan ke dua rentang yang dipakai tabel. Angka
     * pertama pada sel dianggap batas bawah; "< 1.000.000" dianggap di bawah satu
     * juta.
     */
    private function resolveIncomeRange(mixed $value): ?string
    {
        $text = $this->asString($value);

        if ($text === '') {
            return null;
        }

        preg_match_all('/\d[\d.,]*/', $text, $matches);

        $numbers = array_values(array_filter(array_map(
            fn (string $number) => (int) preg_replace('/\D/', '', $number),
            $matches[0] ?? []
        ), fn (int $number) => $number > 0));

        if ($numbers === []) {
            return null;
        }

        if (count($numbers) === 1 && str_contains($text, '<') && $numbers[0] <= 1000000) {
            return '<1000000';
        }

        return min($numbers) < 1000000 ? '<1000000' : '>1000000';
    }

    /**
     * Boolean dari teks Ya/Tidak, Bersedia, Ada, dan sejenisnya. Nilai yang tidak
     * dikenal memakai $default.
     */
    private function toBoolean(mixed $value, bool $default): bool
    {
        return $this->toNullableBoolean($value) ?? $default;
    }

    private function toNullableBoolean(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $text = mb_strtolower($this->asString($value));

        if ($text === '') {
            return null;
        }

        if (in_array($text, ['1', 'ya', 'y', 'yes', 'true', 'ada', 'bersedia', 'sudah', 'positif'], true)) {
            return true;
        }

        if (in_array($text, ['0', 'tidak', 'no', 'tidak ada', 'tidak bersedia', 'belum', 'negatif', 'tidak punya', 'tidak memiliki'], true)) {
            return false;
        }

        return null;
    }

    private function asString(mixed $value): string
    {
        return trim((string) $value);
    }

    private function asNullableString(mixed $value): ?string
    {
        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function asNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->format('Y-m-d');
            }

            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
