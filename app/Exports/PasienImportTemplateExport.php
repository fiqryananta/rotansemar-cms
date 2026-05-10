<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class PasienImportTemplateExport implements FromArray, WithTitle
{
    public function title(): string
    {
        return 'Template Import Pasien';
    }

    public function array(): array
    {
        return [
            [
                'name',
                'nik',
                'birth_date',
                'gender',
                'faskes_id',
                'puskesmas_id',
                'kecamatan_id',
                'kelurahan_id',
                'address',
                'coordinates',
                'treatment_start_date',
                'catatan_kebutuhan',
            ],
            [
                'Budi Santoso',
                '3374010101010001',
                '2000-01-31',
                'laki-laki',
                1,
                1,
                1,
                1,
                'Jl. Contoh No. 1',
                '-6.966667, 110.416664',
                '2026-05-10',
                'Catatan kebutuhan contoh',
            ],
        ];
    }
}
