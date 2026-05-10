<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete old lowercase admin role if it exists
        Role::where('name', 'admin')->delete();

        $adminRole = Role::findOrCreate('Admin', 'web');
        $opdRole = Role::findOrCreate('OPD', 'web');
        $faskesRole = Role::findOrCreate('Faskes', 'web');
        $puskesmasRole = Role::findOrCreate('Puskesmas', 'web');
        $kecamatanRole = Role::findOrCreate('Kecamatan', 'web');
        $kelurahanRole = Role::findOrCreate('Kelurahan', 'web');

        $adminRole->givePermissionTo(Permission::all());

        $opdRole->syncPermissions([
            'dashboard.view',
            'tindak-lanjut.view',
            'tindak-lanjut.verifikasi',
            'tindak-lanjut.riwayat.create',
            'tindak-lanjut.selesai',
        ]);

        $faskesRole->syncPermissions([
            'dashboard.view',
            'pasiens.view',
            'pasiens.create',
        ]);

        $puskesmasRole->syncPermissions([
            'dashboard.view',
            'pasiens.view',
            'pasiens.create',
        ]);

        $kecamatanRole->syncPermissions([
            'dashboard.view',
            'pasiens.view',
            'tindak-lanjut.view',
        ]);

        $kelurahanRole->syncPermissions([
            'dashboard.view',
            'pasiens.view',
            'tindak-lanjut.view',
        ]);
    }
}
