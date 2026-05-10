<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'dashboard.view',

            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            'permissions.view',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',

            'opds.view',
            'opds.create',
            'opds.edit',
            'opds.delete',

            'pekerjaan.view',
            'pekerjaan.create',
            'pekerjaan.edit',
            'pekerjaan.delete',

            'faskes.view',
            'faskes.create',
            'faskes.edit',
            'faskes.delete',

            'kecamatans.view',
            'kecamatans.create',
            'kecamatans.edit',
            'kecamatans.delete',

            'kelurahans.view',
            'kelurahans.create',
            'kelurahans.edit',
            'kelurahans.delete',

            'puskesmas.view',
            'puskesmas.create',
            'puskesmas.edit',
            'puskesmas.delete',

            'jenis-penanganans.view',
            'jenis-penanganans.create',
            'jenis-penanganans.edit',
            'jenis-penanganans.delete',

            'jenis-kebutuhans.view',
            'jenis-kebutuhans.create',
            'jenis-kebutuhans.edit',
            'jenis-kebutuhans.delete',

            'pasiens.view',
            'pasiens.create',
            'pasiens.edit',
            'pasiens.delete',

            'tindak-lanjut.view',
            'tindak-lanjut.verifikasi',
            'tindak-lanjut.riwayat.create',
            'tindak-lanjut.selesai',

            'kegiatan-penyuluhan.view',
            'kegiatan-penyuluhan.create',
            'kegiatan-penyuluhan.edit',
            'kegiatan-penyuluhan.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $adminRole = Role::query()
            ->whereRaw('LOWER(name) = ?', ['admin'])
            ->where('guard_name', 'web')
            ->first();

        if ($adminRole && $adminRole->name !== 'Admin') {
            $namedAdminRole = Role::query()
                ->where('name', 'Admin')
                ->where('guard_name', 'web')
                ->first();

            if ($namedAdminRole) {
                $adminRole = $namedAdminRole;
            } else {
                $adminRole->update(['name' => 'Admin']);
            }
        }

        if (!$adminRole) {
            $adminRole = Role::findOrCreate('Admin', 'web');
        }

        $adminRole->syncPermissions(Permission::all());
    }
}
