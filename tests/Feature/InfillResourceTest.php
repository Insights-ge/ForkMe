<?php

use App\Enums\ComponentType;
use App\Filament\Resources\Components\Pages\EditComponent;
use App\Filament\Resources\Infills\Pages\CreateInfill;
use App\Filament\Resources\Infills\Pages\EditInfill;
use App\Filament\Resources\Infills\Pages\ListInfills;
use App\Models\Component;
use App\Models\Infill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->actingAs(User::query()->where('email', 'demo@demo.com')->firstOrFail());
});

test('infills can be searched by supplier code', function () {
    $infill = Infill::factory()->create();
    Livewire::test(ListInfills::class)->searchTable($infill->unique_code)->assertCanSeeTableRecords([$infill]);
});

test('infills can be created with exceptions for any component type', function () {
    $components = collect(ComponentType::cases())->map(fn ($type) => Component::factory()->ofType($type)->create());
    Livewire::test(CreateInfill::class)->fillForm([
        'code' => 'მინ9999', 'description' => 'ტესტი', 'unique_code' => '000099999',
        'incompatibleComponents' => $components->pluck('id')->all(),
    ])->call('create')->assertHasNoFormErrors();
    $infill = Infill::query()->where('unique_code', '000099999')->firstOrFail();
    expect($infill->incompatibleComponents()->count())->toBe(5);
});

test('clearing exceptions allows all components', function () {
    $infill = Infill::factory()->create();
    $infill->incompatibleComponents()->attach(Component::factory()->create());
    Livewire::test(EditInfill::class, ['record' => $infill->id])
        ->fillForm(['incompatibleComponents' => []])->call('save')->assertHasNoFormErrors();
    expect($infill->incompatibleComponents()->count())->toBe(0);
});

test('duplicate supplier identifiers are rejected', function () {
    $infill = Infill::factory()->create();
    Livewire::test(CreateInfill::class)->fillForm([
        'code' => 'მინ9999', 'description' => 'ტესტი', 'unique_code' => $infill->unique_code,
    ])->call('create')->assertHasFormErrors(['unique_code' => 'unique']);
});

test('component editing updates infill exceptions', function () {
    $component = Component::factory()->create();
    $infill = Infill::factory()->create();
    Livewire::test(EditComponent::class, ['record' => $component->id])
        ->fillForm(['incompatibleInfills' => [$infill->id]])->call('save')->assertHasNoFormErrors();
    expect($infill->incompatibleComponents()->whereKey($component->id)->exists())->toBeTrue();
});

test('users without permissions cannot access infills', function () {
    $this->actingAs(User::factory()->create());
    Livewire::test(ListInfills::class)->assertForbidden();
    Livewire::test(CreateInfill::class)->assertForbidden();
});
