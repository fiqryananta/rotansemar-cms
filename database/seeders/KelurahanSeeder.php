<?php

namespace Database\Seeders;

use App\Models\Kelurahan;
use App\Models\Kecamatan;
use Illuminate\Database\Seeder;

class KelurahanSeeder extends Seeder
{
    public function run(): void
    {
        // Get kecamatan records
        $kecamatan = Kecamatan::orderBy('id')->get();

        if ($kecamatan->count() === 0) {
            return; // Skip if no kecamatan exists
        }

        $data = [
            ['name' => 'Banyumanik', 'kecamatan_id' => $kecamatan[0]->id],
            ['name' => 'Bulu Lor', 'kecamatan_id' => $kecamatan[1]->id],
            ['name' => 'Kuningan', 'kecamatan_id' => $kecamatan[1]->id],
            ['name' => 'Panggung Lor', 'kecamatan_id' => $kecamatan[1]->id],
        ];

        foreach ($data as $item) {
            Kelurahan::firstOrCreate(
                ['name' => $item['name'], 'kecamatan_id' => $item['kecamatan_id']],
                $item
            );
        }
    }
}
