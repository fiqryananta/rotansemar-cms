<?php

namespace Database\Seeders;

use App\Models\Faskes;
use Illuminate\Database\Seeder;

class FaskesSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'RSUD K.R.M.T Wongsonegoro'],
        ];

        foreach ($data as $item) {
            Faskes::firstOrCreate(['name' => $item['name']], $item);
        }
    }
}
