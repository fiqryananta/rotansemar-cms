<?php

namespace Database\Seeders;

use App\Models\Opd;
use Illuminate\Database\Seeder;

class OpdSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'Dinas Kesehatan'],
            ['name' => 'Dinas Pendidikan'],
            ['name' => 'Dinas Sosial'],
        ];

        foreach ($data as $item) {
            Opd::firstOrCreate(['name' => $item['name']], $item);
        }
    }
}
