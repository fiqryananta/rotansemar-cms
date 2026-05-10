<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MedicationPickupPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'medication-pickups.view',
            'medication-pickups.create',
            'medication-pickups.edit',
            'medication-pickups.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }
    }
}
