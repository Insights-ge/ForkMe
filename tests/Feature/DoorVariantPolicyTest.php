<?php

use App\Enums\Role as RoleEnum;
use App\Models\DoorVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

test('seeded roles follow the configured door variant import permissions', function (string $email, bool $canImport) {
    expect(User::where('email', $email)->first()->can('import', DoorVariant::class))->toBe($canImport);
})->with([
    ['demo@demo.com', true],
    ['admin@ltb.ge', false],
]);

test('users cannot import door variants', function () {
    $user = User::factory()->create();
    $user->assignRole(RoleEnum::User->value);

    expect($user->can('import', DoorVariant::class))->toBeFalse();
});
