<?php

namespace Database\Seeders;

use App\Models\Puskesmas;
use Illuminate\Database\Seeder;

class PuskesmasSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'Puskesmas Bulu Lor'],
        ];

        foreach ($data as $item) {
            Puskesmas::firstOrCreate(['name' => $item['name']], $item);
        }
    }
}
