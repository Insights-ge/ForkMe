<?php

use App\Data\BuilderBrowserPayloadData;
use App\Data\BuilderCatalogData;
use App\Enums\ComponentType;
use App\Enums\DoorColor;
use App\Filament\Pages\SlidingDoorBuilder;
use App\Models\DoorVariant;
use App\Models\Infill;
use App\Models\User;
use App\Services\BuilderCatalogReadService;
use Database\Seeders\GeneralSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(GeneralSettingsSeeder::class);
});

test('the browser contract keeps empty catalog arrays separate from the metadata object', function () {
    $payload = new BuilderBrowserPayloadData(new BuilderCatalogData(models: [], infills: []));
    $decoded = json_decode(json_encode($payload, JSON_THROW_ON_ERROR), flags: JSON_THROW_ON_ERROR);

    expect(array_keys(get_object_vars($decoded)))->toBe(['catalog', 'metadata']);
    expect($decoded->catalog->models)->toBe([]);
    expect($decoded->catalog->infills)->toBe([]);
    expect($decoded->metadata)->toBeInstanceOf(stdClass::class);
    expect(get_object_vars($decoded->metadata))->toBe([]);
});

test('the browser payload serializes catalog enums references and optional values', function () {
    $variant = DoorVariant::factory()->withoutPartition()->create(['color' => DoorColor::Black]);
    $variant->doorModel->update(['not_recommended_height_min' => null, 'not_recommended_height_max' => null]);
    $infill = Infill::factory()->create();
    $variant->sideProfile->incompatibleInfills()->attach($infill);

    $payload = new BuilderBrowserPayloadData(app(BuilderCatalogReadService::class)->read());
    $decoded = json_decode(json_encode($payload, JSON_THROW_ON_ERROR), flags: JSON_THROW_ON_ERROR);
    $model = $decoded->catalog->models[0];
    $door = $model->variants[0];

    expect($model->notRecommendedHeightMin)->toBeNull();
    expect($door->color)->toBe('black');
    expect($door->hasPartition)->toBeFalse();
    expect($door->hasSoftCloseMechanism)->toBeTrue();
    expect($door->components[0]->type)->toBe('side_profile');
    expect($door->components[0]->incompatibleInfillIds)->toBe([$infill->id]);
    expect($decoded->catalog->infills[0]->id)->toBe($infill->id);
    expect(get_object_vars($decoded->metadata))->toBe([]);
});

test('the authorized page embeds an escaped payload without displaying catalog selections', function () {
    $name = '</script><script>alert("catalog")</script>';
    $variant = DoorVariant::factory()->create(['product_full_name' => $name]);
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('View:SlidingDoorBuilder', 'web'));
    $this->actingAs($user);

    Livewire::test(SlidingDoorBuilder::class)
        ->assertSuccessful()
        ->assertViewHas('builderPayload', fn (BuilderBrowserPayloadData $payload): bool => $payload->catalog->models[0]->variants[0]->id === $variant->id);

    $this->get(SlidingDoorBuilder::getUrl(panel: 'admin'))
        ->assertOk()
        ->assertSee('payload:', false)
        ->assertDontSee($name, false);
});

test('unauthorized requests never read the catalog', function () {
    $service = Mockery::mock(BuilderCatalogReadService::class);
    $service->shouldNotReceive('read');
    app()->instance(BuilderCatalogReadService::class, $service);

    $this->get(SlidingDoorBuilder::getUrl(panel: 'admin'))->assertRedirect(route('filament.admin.auth.login'));

    $this->actingAs(User::factory()->create())
        ->get(SlidingDoorBuilder::getUrl(panel: 'admin'))->assertForbidden();
    Livewire::test(SlidingDoorBuilder::class)->assertForbidden();
});

test('the published schema uses the application enum values', function () {
    $schema = json_decode(file_get_contents(resource_path('schemas/sliding-door-builder-payload.schema.json')), true, flags: JSON_THROW_ON_ERROR);

    expect($schema['$defs']['variant']['properties']['color']['enum'])->toBe(array_column(DoorColor::cases(), 'value'));
    expect($schema['$defs']['component']['properties']['type']['enum'])->toBe(array_column(ComponentType::cases(), 'value'));
});
