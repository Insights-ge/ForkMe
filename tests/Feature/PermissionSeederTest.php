<?php

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\AdminPermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SuperAdminPermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as PermissionRole;

uses(RefreshDatabase::class);

test('role seeding only creates roles and is repeatable', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(RoleSeeder::class);

    expect(PermissionRole::query()->pluck('name')->sort()->values()->all())
        ->toBe(collect(Role::cases())->map(fn (Role $role) => $role->value)->sort()->values()->all());
    expect(Permission::query()->count())->toBe(0);
    expect(User::query()->count())->toBe(0);
});

test('super admin receives every permission including existing custom permissions', function () {
    $this->seed(RoleSeeder::class);
    Permission::findOrCreate('View:ExistingCustomPage', 'web');
    $this->seed(AdminPermissionSeeder::class);
    $this->seed(SuperAdminPermissionSeeder::class);
    $this->seed(SuperAdminPermissionSeeder::class);

    $role = PermissionRole::findByName(Role::SuperAdmin->value, 'web');
    expect($role->permissions->pluck('name')->sort()->values()->all())
        ->toBe(Permission::query()->where('guard_name', 'web')->pluck('name')->sort()->values()->all());
    expect($role->hasPermissionTo('Import:DoorVariant'))->toBeTrue();
    expect($role->hasPermissionTo('View:SlidingDoorBuilder'))->toBeTrue();
    expect($role->hasPermissionTo('create-backup'))->toBeTrue();
});

test('admin permissions reproduce the local database snapshot', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(SuperAdminPermissionSeeder::class);
    PermissionRole::findByName(Role::Admin->value, 'web')->givePermissionTo('create-backup');
    $this->seed(AdminPermissionSeeder::class);
    $this->seed(AdminPermissionSeeder::class);

    $role = PermissionRole::findByName(Role::Admin->value, 'web');
    expect($role->permissions)->toHaveCount(32);
    expect($role->hasPermissionTo('View:SlidingDoorBuilder'))->toBeTrue();
    expect($role->hasPermissionTo('DeleteAny:Infill'))->toBeTrue();
    expect($role->hasPermissionTo('Import:Component'))->toBeTrue();
    expect($role->hasPermissionTo('Import:DoorVariant'))->toBeFalse();
    expect($role->hasPermissionTo('Delete:DoorModel'))->toBeFalse();
    expect($role->hasPermissionTo('create-backup'))->toBeFalse();
    expect(PermissionRole::findByName(Role::User->value, 'web')->permissions)->toBeEmpty();
});

test('user seeding assigns the default accounts to their roles', function () {
    $this->seed([RoleSeeder::class, UserSeeder::class]);

    expect(User::query()->where('email', 'demo@demo.com')->firstOrFail()->hasRole(Role::SuperAdmin))->toBeTrue();
    expect(User::query()->where('email', 'admin@ltb.ge')->firstOrFail()->hasRole(Role::Admin))->toBeTrue();
});
