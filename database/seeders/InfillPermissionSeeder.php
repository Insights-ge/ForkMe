<?php

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class InfillPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [];

        foreach (['ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny'] as $action) {
            $permissions[] = Permission::findOrCreate("{$action}:Infill", 'web');
        }

        Role::query()->with('permissions')->whereIn('name', [RoleEnum::Admin->value, RoleEnum::SuperAdmin->value])
            ->where('guard_name', 'web')
            ->each(fn (Role $role) => $role->givePermissionTo($permissions));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
