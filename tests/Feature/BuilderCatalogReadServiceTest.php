<?php

use App\Data\BuilderCatalogData;
use App\Data\BuilderComponentCompatibilityData;
use App\Enums\ComponentType;
use App\Enums\DoorColor;
use App\Enums\DoorModelStatus;
use App\Models\Component;
use App\Models\DoorModel;
use App\Models\DoorVariant;
use App\Models\Infill;
use App\Services\BuilderCatalogReadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('the catalog contains only active models and their variants', function () {
    $active = DoorModel::factory()->create(['name' => 'Active system']);
    $inactive = DoorModel::factory()->create(['status' => DoorModelStatus::Passive]);
    $variant = DoorVariant::factory()->for($active)->create(['color' => DoorColor::Black]);
    DoorVariant::factory()->for($inactive)->create();

    $catalog = app(BuilderCatalogReadService::class)->read();

    expect($catalog)->toBeInstanceOf(BuilderCatalogData::class);
    expect($catalog->models)->toHaveCount(1);
    $model = $catalog->models[0];
    expect($model->id)->toBe($active->id)
        ->and($model->minHeight)->toBe($active->min_height)
        ->and($model->maxWidth)->toBe($active->max_width)
        ->and($model->notRecommendedHeightMin)->toBe($active->not_recommended_height_min)
        ->and($model->variants)->toHaveCount(1)
        ->and($model->variants[0]->id)->toBe($variant->id)
        ->and($model->variants[0]->color)->toBe(DoorColor::Black)
        ->and($model->variants[0]->heightRangeMin)->toBe($variant->height_range_min)
        ->and($model->variants[0]->heightRangeMax)->toBe($variant->height_range_max);
});

test('component exclusions are preserved without excluding infills from the catalog', function () {
    $variant = DoorVariant::factory()->create();
    $excluded = Infill::factory()->create();
    $allowed = Infill::factory()->create();
    $variant->sideProfile->incompatibleInfills()->attach($excluded);
    $variant->partition->incompatibleInfills()->attach($allowed);

    $catalog = app(BuilderCatalogReadService::class)->read();
    $components = collect($catalog->models[0]->variants[0]->components)->keyBy(fn (BuilderComponentCompatibilityData $component): string => $component->type->value);

    expect($components[ComponentType::SideProfile->value]->incompatibleInfillIds)->toBe([$excluded->id]);
    expect($components[ComponentType::Partition->value]->incompatibleInfillIds)->toBe([$allowed->id]);
    expect($components[ComponentType::TopRail->value]->incompatibleInfillIds)->toBe([]);
    expect($components[ComponentType::SoftCloseMechanism->value]->id)->toBe($variant->soft_close_mechanism_component_id);
    expect(array_column($catalog->infills, 'id'))->toEqualCanonicalizing([$excluded->id, $allowed->id]);
});

test('models without variants and variants without partitions remain readable', function () {
    $empty = DoorModel::factory()->create([
        'name' => 'A system', 'not_recommended_height_min' => null, 'not_recommended_height_max' => null,
    ]);
    $variant = DoorVariant::factory()->withoutPartition()->create();

    $catalog = app(BuilderCatalogReadService::class)->read();

    $models = collect($catalog->models)->keyBy('id');
    expect($models[$empty->id]->variants)->toBe([]);
    expect($models[$empty->id]->notRecommendedHeightMin)->toBeNull();
    expect($models[$empty->id]->notRecommendedHeightMax)->toBeNull();
    expect(array_column($models[$variant->door_model_id]->variants[0]->components, 'type'))
        ->not->toContain(ComponentType::Partition);
});

test('an empty database returns an empty catalog', function () {
    $catalog = app(BuilderCatalogReadService::class)->read();

    expect($catalog->models)->toBe([])->and($catalog->infills)->toBe([]);
});

test('catalog reads use a fixed number of queries as the catalog grows', function () {
    DoorVariant::factory()->create();
    Infill::factory()->create();
    $service = app(BuilderCatalogReadService::class);

    DB::enableQueryLog();
    try {
        $service->read();
        $smallCatalogQueries = count(DB::getQueryLog());
        DB::disableQueryLog();
        DoorVariant::factory()->count(4)->create();
        Infill::factory()->count(4)->create();
        DB::flushQueryLog();
        DB::enableQueryLog();
        $service->read();

        expect(count(DB::getQueryLog()))->toBe($smallCatalogQueries);
    } finally {
        DB::disableQueryLog();
        DB::flushQueryLog();
    }
});

test('catalog data does not serialize supplier identifiers or admin fields', function () {
    DoorVariant::factory()->create(['unique_code' => 'PRIVATE-VARIANT']);
    Component::query()->update(['description' => 'PRIVATE-COMPONENT']);
    Infill::factory()->create(['unique_code' => 'PRIVATE-INFILL']);

    $json = json_encode(app(BuilderCatalogReadService::class)->read(), JSON_THROW_ON_ERROR);

    expect($json)->not->toContain('PRIVATE-VARIANT', 'PRIVATE-COMPONENT', 'PRIVATE-INFILL', 'unique_code', 'created_at', 'updated_at', 'status');
});
