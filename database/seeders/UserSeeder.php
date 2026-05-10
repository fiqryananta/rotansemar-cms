<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->firstOrCreate([
            'email' => 'dinkes@semarangkota.go.id',
        ], [
            'name' => 'Dinkes Semarang',
            'password' => Hash::make('Admin#123'),
            'email_verified_at' => now(),
        ]);

        $admin->syncRoles(['Admin']);
    }
}
