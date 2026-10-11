<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ImpersonatePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'users.impersonate',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Role admin dicari tanpa peduli huruf besar/kecil, karena PermissionSeeder
        // sudah me-rename role 'admin' menjadi 'Admin'.
        $adminRole = Role::query()
            ->whereRaw('LOWER(name) = ?', ['admin'])
            ->where('guard_name', 'web')
            ->first();

        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }
    }
}
