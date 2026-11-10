<?php

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Exact local admin permissions captured on 2026-11-10.
        $permissions = [
            'Create:Component',
            'Create:DoorModel',
            'Create:DoorVariant',
            'Create:Infill',
            'Create:Role',
            'Create:User',
            'Delete:Component',
            'Delete:DoorVariant',
            'Delete:Infill',
            'Delete:Role',
            'Delete:User',
            'DeleteAny:Infill',
            'Import:Component',
            'Update:Component',
            'Update:DoorModel',
            'Update:DoorVariant',
            'Update:Infill',
            'Update:Role',
            'Update:User',
            'View:Component',
            'View:DoorModel',
            'View:DoorVariant',
            'View:Infill',
            'View:Role',
            'View:SlidingDoorBuilder',
            'View:User',
            'ViewAny:Component',
            'ViewAny:DoorModel',
            'ViewAny:DoorVariant',
            'ViewAny:Infill',
            'ViewAny:Role',
            'ViewAny:User',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        Role::findByName(RoleEnum::Admin->value, 'web')->syncPermissions($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
