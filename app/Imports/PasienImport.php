<?php

namespace App\Imports;

use App\Models\Pasien;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PasienImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithChunkReading, WithBatchInserts
{
    private int $inserted = 0;
    private int $updated = 0;
    private int $skipped = 0;

    /** @var array<int, string> */
    private array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $line = $index + 2;

            $payload = [
                'name' => $this->asString($row['name'] ?? null),
                'nik' => $this->asString($row['nik'] ?? null),
                'birth_date' => $this->parseDate($row['birth_date'] ?? null),
                'gender' => $this->asString($row['gender'] ?? null),
                'faskes_id' => $this->asNullableInt($row['faskes_id'] ?? null),
                'puskesmas_id' => $this->asNullableInt($row['puskesmas_id'] ?? null),
                'kecamatan_id' => $this->asNullableInt($row['kecamatan_id'] ?? null),
                'kelurahan_id' => $this->asNullableInt($row['kelurahan_id'] ?? null),
                'address' => $this->asString($row['address'] ?? null),
                'coordinates' => $this->asNullableString($row['coordinates'] ?? null),
                'treatment_start_date' => $this->parseNullableDate($row['treatment_start_date'] ?? null),
                'catatan_kebutuhan' => $this->asNullableString($row['catatan_kebutuhan'] ?? null),
            ];

            $validator = Validator::make($payload, [
                'name' => ['required', 'string', 'max:255'],
                'nik' => ['required', 'digits:16'],
                'birth_date' => ['required', 'date'],
                'gender' => ['required', Rule::in(['laki-laki', 'perempuan'])],
                'faskes_id' => ['required', 'exists:faskes,id'],
                'puskesmas_id' => ['required', 'exists:puskesmas,id'],
                'kecamatan_id' => ['required', 'exists:kecamatans,id'],
                'kelurahan_id' => ['required', 'exists:kelurahans,id'],
                'address' => ['required', 'string'],
                'coordinates' => ['nullable', 'string', 'max:255'],
                'treatment_start_date' => ['nullable', 'date'],
                'catatan_kebutuhan' => ['nullable', 'string'],
            ]);

            if ($validator->fails()) {
                $this->skipped++;
                $messages = implode('; ', $validator->errors()->all());
                $this->errors[] = "Baris {$line}: {$messages}";
                continue;
            }

            $data = $validator->validated();
            $data['ever_received_assistance'] = false;
            $data['willing_to_help'] = false;
            $data['respondent'] = false;
            $data['pregnancy_status'] = false;
            $data['comorbid_status'] = false;
            $data['smoking_behavior'] = false;
            $data['family_smoking_status'] = false;
            $data['jkn_ownership'] = false;

            $existing = Pasien::query()->where('nik', $data['nik'])->first();
            if ($existing) {
                $existing->update($data);
                $this->updated++;
                continue;
            }

            Pasien::query()->create($data);
            $this->inserted++;
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

    public function chunkSize(): int
    {
        return 250;
    }

    public function batchSize(): int
    {
        return 250;
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

        return (int) $value;
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

    private function parseNullableDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $this->parseDate($value);
    }
}
