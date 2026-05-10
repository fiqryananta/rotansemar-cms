<?php

namespace Database\Seeders;

use App\Models\Kecamatan;
use Illuminate\Database\Seeder;

class KecamatanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'Banyumanik'],
            ['name' => 'Semarang Utara'],
            ['name' => 'Semarang Timur'],
            ['name' => 'Semarang Selatan'],
            ['name' => 'Semarang Barat'],
            ['name' => 'Semarang Tengah'],
            ['name' => 'Gajahmungkur'],
            ['name' => 'Gunungpati'],
            ['name' => 'Candisari'],
            ['name' => 'Tembalang'],
            ['name' => 'Pedurungan'],
            ['name' => 'Mijen'],
            ['name' => 'Ngaliyan'],
            ['name' => 'Gayamsari'],
            ['name' => 'Genuk'],
            ['name' => 'Tugu'],
        ];

        foreach ($data as $item) {
            Kecamatan::firstOrCreate(['name' => $item['name']], $item);
        }
    }
}
