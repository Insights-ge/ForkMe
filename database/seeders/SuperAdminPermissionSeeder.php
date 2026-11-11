<?php

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SuperAdminPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->callSilent('shield:generate', [
            '--all' => true,
            '--panel' => 'admin',
            '--option' => 'permissions',
            '--no-interaction' => true,
        ]);

        foreach (['create-backup', 'delete-backup', 'download-backup', 'DeleteAny:Infill'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        Role::findByName(RoleEnum::SuperAdmin->value, 'web')
            ->syncPermissions(Permission::query()->where('guard_name', 'web')->get());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
