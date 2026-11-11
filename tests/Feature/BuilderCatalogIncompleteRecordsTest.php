<?php

use App\Models\Component;
use App\Models\DoorModel;
use App\Models\DoorVariant;
use App\Models\Infill;
use App\Services\BuilderCatalogReadService;
use App\Services\BuilderCatalogRecordValidator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('missing required frame components omit only the affected variant', function (string $relation) {
    $broken = DoorVariant::factory()->create();
    $valid = DoorVariant::factory()->create();
    $missingId = $broken->{$relation}->id;
    Component::addGlobalScope('unavailable', fn (Builder $query) => $query->whereKeyNot($missingId));

    try {
        $catalog = app(BuilderCatalogReadService::class)->read();
        expect(array_column($catalog->models, 'id'))->toBe([$valid->door_model_id]);
        expect($catalog->models[0]->variants[0]->id)->toBe($valid->id);
    } finally {
        Component::clearBootedModels();
    }
})->with(['sideProfile', 'topRail', 'bottomRail']);

test('missing optional components disable only that option', function (string $relation, string $availability) {
    $variant = DoorVariant::factory()->create();
    $missingId = $variant->{$relation}->id;
    Component::addGlobalScope('unavailable', fn (Builder $query) => $query->whereKeyNot($missingId));

    try {
        $catalog = app(BuilderCatalogReadService::class)->read();
        $result = $catalog->models[0]->variants[0];
        expect($result->id)->toBe($variant->id);
        expect($result->{$availability})->toBeFalse();
        expect(array_column($result->components, 'id'))->not->toContain($missingId);
    } finally {
        Component::clearBootedModels();
    }
})->with([
    ['partition', 'hasPartition'],
    ['softCloseMechanism', 'hasSoftCloseMechanism'],
]);

test('mismatched required component types make the variant unavailable', function () {
    $variant = DoorVariant::factory()->create();
    $variant->update(['side_profile_component_id' => $variant->top_rail_component_id]);

    expect(app(BuilderCatalogReadService::class)->read()->models)->toBe([]);
});

test('mismatched optional component types are not reported as available', function () {
    $variant = DoorVariant::factory()->create();
    $variant->update([
        'partition_component_id' => $variant->side_profile_component_id,
        'soft_close_mechanism_component_id' => $variant->top_rail_component_id,
    ]);

    $result = app(BuilderCatalogReadService::class)->read()->models[0]->variants[0];

    expect($result->hasPartition)->toBeFalse();
    expect($result->hasSoftCloseMechanism)->toBeFalse();
    expect($result->components)->toHaveCount(3);
});

test('incomplete models are omitted', function (array $attributes) {
    $model = DoorModel::factory()->create($attributes);
    DoorVariant::factory()->for($model)->create();

    expect(app(BuilderCatalogReadService::class)->read()->models)->toBe([]);
})->with([
    [['name' => '   ']],
    [['min_height' => 0]],
    [['min_height' => 2000, 'max_height' => 1500]],
    [['min_width' => 0]],
    [['min_width' => 1000, 'max_width' => 500]],
]);

test('incomplete variants are omitted', function (array $attributes) {
    DoorVariant::factory()->create($attributes);

    expect(app(BuilderCatalogReadService::class)->read()->models)->toBe([]);
})->with([
    [['product_full_name' => '   ']],
    [['frame_design_code' => '   ']],
    [['height_range_min' => 0]],
    [['height_range_min' => 2000, 'height_range_max' => 1500]],
]);

test('invalid stored enum values are rejected without invoking enum casts', function () {
    $variant = DoorVariant::factory()->create()->load(['sideProfile', 'topRail', 'bottomRail']);
    $variant->setRawAttributes(array_replace($variant->getAttributes(), ['color' => 'unknown']), sync: true);

    expect(app(BuilderCatalogRecordValidator::class)->isVariantUsable($variant))->toBeFalse();
});

test('incomplete advisory ranges are omitted without removing a usable system', function (array $attributes) {
    $model = DoorModel::factory()->create($attributes);
    DoorVariant::factory()->for($model)->create();

    $result = app(BuilderCatalogReadService::class)->read()->models[0];

    expect($result->notRecommendedHeightMin)->toBeNull();
    expect($result->notRecommendedHeightMax)->toBeNull();
})->with([
    [['not_recommended_height_min' => null]],
    [['not_recommended_height_max' => null]],
    [['not_recommended_height_min' => 0]],
    [['not_recommended_height_min' => 2700, 'not_recommended_height_max' => 2600]],
]);

test('incomplete infills are omitted without adding fallback records', function () {
    $valid = Infill::factory()->create();
    Infill::factory()->create(['code' => '   ']);
    Infill::factory()->create(['description' => '   ']);

    $catalog = app(BuilderCatalogReadService::class)->read();

    expect(array_column($catalog->infills, 'id'))->toBe([$valid->id]);
    expect($catalog->models)->toBe([]);
});
