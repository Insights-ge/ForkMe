<?php

use App\Enums\ComponentType;
use App\Models\Component;
use App\Models\DoorVariant;
use Database\Seeders\DoorCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DoorCatalogSeeder::class);
});

test('every variant component slot points to a component of that slot type', function () {
    $variants = DoorVariant::query()
        ->with(['sideProfile', 'topRail', 'bottomRail', 'partition', 'softCloseMechanism'])
        ->get();

    expect($variants)->not->toBeEmpty();

    $variants->each(function (DoorVariant $variant): void {
        expect($variant->sideProfile->type)->toBe(ComponentType::SideProfile)
            ->and($variant->topRail->type)->toBe(ComponentType::TopRail)
            ->and($variant->bottomRail->type)->toBe(ComponentType::BottomRail)
            ->and($variant->partition?->type ?? ComponentType::Partition)->toBe(ComponentType::Partition)
            ->and($variant->softCloseMechanism->type)->toBe(ComponentType::SoftCloseMechanism);
    });
});

test('a component used in slots of different types is stored once per type', function () {
    expect(Component::query()->where('unique_code', '000004465')->pluck('type')->all())
        ->toEqualCanonicalizing([ComponentType::SideProfile, ComponentType::TopRail]);
});
