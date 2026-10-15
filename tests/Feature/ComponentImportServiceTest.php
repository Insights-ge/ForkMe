<?php

use App\Data\ComponentImportPreviewData;
use App\Data\ComponentImportResultData;
use App\Data\ComponentImportRowData;
use App\Enums\ComponentType;
use App\Enums\DoorColor;
use App\Models\Component;
use App\Models\DoorVariant;
use App\Services\ComponentImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @param  list<ComponentImportRowData>  $rows
 */
function importComponentRows(array $rows): ComponentImportResultData
{
    return app(ComponentImportService::class)->import($rows);
}

/**
 * @param  list<ComponentImportRowData>  $rows
 */
function previewComponentRows(array $rows): ComponentImportPreviewData
{
    return app(ComponentImportService::class)->preview($rows);
}

/**
 * @return array{created: int, updated: int, unchanged: int}
 */
function componentImportCounts(ComponentImportResultData|ComponentImportPreviewData $result): array
{
    return $result instanceof ComponentImportResultData
        ? ['created' => $result->createdCount, 'updated' => $result->updatedCount, 'unchanged' => $result->unchangedCount]
        : ['created' => $result->toCreateCount, 'updated' => $result->toUpdateCount, 'unchanged' => $result->unchangedCount];
}

/**
 * The saved side profile with the default test unique code.
 */
function savedSideProfile(array $attributes = []): Component
{
    return Component::factory()->ofType(ComponentType::SideProfile)->create(array_merge([
        'unique_code' => '000015193',
        'code' => 'გალუ4108',
        'description' => 'პროფილი. ნობელალუმინა XGJB-1058P ვწ. ორმხრივი გვერდი',
        'color' => DoorColor::Black,
    ], $attributes));
}

test('a new component is created from the file', function () {
    $result = importComponentRows([componentImportRowData()]);

    expect($result->hasErrors())->toBeFalse()
        ->and(componentImportCounts($result))->toBe(['created' => 1, 'updated' => 0, 'unchanged' => 0]);

    $component = Component::query()->sole();

    expect($component->type)->toBe(ComponentType::SideProfile)
        ->and($component->unique_code)->toBe('000015193')
        ->and($component->code)->toBe('გალუ4108')
        ->and($component->description)->toBe('პროფილი. ნობელალუმინა XGJB-1058P ვწ. ორმხრივი გვერდი')
        ->and($component->color)->toBe(DoorColor::Black);
});

test('an empty colour is saved as no colour', function () {
    importComponentRows([componentImportRowData(['type' => 'ნელი დაკეტვის მექანიზმი', 'color' => ''])]);

    expect(Component::query()->sole()->color)->toBeNull();
});

test('an existing component is updated from the file', function () {
    $component = savedSideProfile();

    $result = importComponentRows([componentImportRowData(['code' => 'გალუ9999', 'description' => 'ახალი აღწერა', 'color' => 'ინოქსი'])]);

    expect(componentImportCounts($result))->toBe(['created' => 0, 'updated' => 1, 'unchanged' => 0])
        ->and(Component::query()->count())->toBe(1);

    $component->refresh();

    expect($component->code)->toBe('გალუ9999')
        ->and($component->description)->toBe('ახალი აღწერა')
        ->and($component->color)->toBe(DoorColor::Inox);
});

test('an empty colour in the file removes the saved colour', function () {
    $component = savedSideProfile();

    importComponentRows([componentImportRowData(['color' => ''])]);

    expect($component->refresh()->color)->toBeNull();
});

test('variants using an updated component show the new values', function () {
    $component = savedSideProfile();
    $variant = DoorVariant::factory()->create(['side_profile_component_id' => $component->id]);

    importComponentRows([componentImportRowData(['description' => 'ახალი აღწერა'])]);

    expect($variant->refresh()->sideProfile->description)->toBe('ახალი აღწერა');
});

test('an existing component with the same values is counted as unchanged and not saved', function () {
    $component = savedSideProfile();
    $updatedAt = $component->updated_at;
    $this->travel(1)->minute();

    $result = importComponentRows([componentImportRowData()]);

    expect(componentImportCounts($result))->toBe(['created' => 0, 'updated' => 0, 'unchanged' => 1])
        ->and($component->refresh()->updated_at->equalTo($updatedAt))->toBeTrue();
});

test('a component with the same unique code but another type is created separately', function () {
    $topRail = Component::factory()->ofType(ComponentType::TopRail)->create(['unique_code' => '000016298', 'description' => 'შენახული']);
    $topRailAttributes = $topRail->fresh()->getAttributes();

    $result = importComponentRows([componentImportRowData(['type' => 'ტიხარი', 'uniqueCode' => '000016298', 'description' => 'სხვა აღწერა'])]);

    expect(componentImportCounts($result))->toBe(['created' => 1, 'updated' => 0, 'unchanged' => 0])
        ->and($topRail->fresh()->getAttributes())->toBe($topRailAttributes)
        ->and(Component::query()->where('unique_code', '000016298')->where('type', ComponentType::Partition)->sole()->description)->toBe('სხვა აღწერა');
});

test('components that are not in the file are not changed', function () {
    $otherComponent = Component::factory()->create();
    $otherComponentAttributes = $otherComponent->fresh()->getAttributes();

    importComponentRows([componentImportRowData()]);

    expect(Component::query()->count())->toBe(2)
        ->and($otherComponent->fresh()->getAttributes())->toBe($otherComponentAttributes);
});

test('nothing is saved when any row fails', function () {
    $component = savedSideProfile();
    $componentAttributes = $component->fresh()->getAttributes();

    $result = importComponentRows([
        componentImportRowData(['description' => 'ახალი აღწერა']),
        componentImportRowData(['rowNumber' => 3, 'type' => 'ტიხარი', 'uniqueCode' => '000016298']),
        componentImportRowData(['rowNumber' => 4, 'type' => 'ტიხარი', 'uniqueCode' => '000016304', 'color' => 'black']),
    ]);

    expect($result->hasErrors())->toBeTrue()
        ->and($result->errors->errors[0]->rowNumber)->toBe(4)
        ->and(componentImportCounts($result))->toBe(['created' => 0, 'updated' => 0, 'unchanged' => 0])
        ->and(Component::query()->count())->toBe(1)
        ->and($component->fresh()->getAttributes())->toBe($componentAttributes);
});

test('a duplicate unique code and type in the file fails the import', function () {
    $result = importComponentRows([
        componentImportRowData(),
        componentImportRowData(['rowNumber' => 3, 'description' => 'სხვა აღწერა']),
    ]);

    expect($result->hasErrors())->toBeTrue()
        ->and($result->errors->errors[0]->column)->toBe('unique_code')
        ->and(Component::query()->count())->toBe(0);
});

test('the preview counts what the import would do without saving anything', function () {
    $unchangedComponent = savedSideProfile();
    $changedComponent = savedSideProfile(['unique_code' => '000015192', 'color' => DoorColor::Inox]);

    $preview = previewComponentRows([
        componentImportRowData(),
        componentImportRowData(['rowNumber' => 3, 'uniqueCode' => '000015192', 'description' => 'ახალი აღწერა', 'color' => 'ინოქსი']),
        componentImportRowData(['rowNumber' => 4, 'uniqueCode' => '000099999']),
        componentImportRowData(['rowNumber' => 5, 'type' => 'ტიხარი']),
    ]);

    expect($preview->hasErrors())->toBeFalse()
        ->and(componentImportCounts($preview))->toBe(['created' => 2, 'updated' => 1, 'unchanged' => 1])
        ->and(Component::query()->count())->toBe(2)
        ->and($changedComponent->refresh()->description)->toBe($unchangedComponent->description);
});

test('a preview with errors returns the errors and no counts', function () {
    $preview = previewComponentRows([componentImportRowData(['type' => 'უცნობი'])]);

    expect($preview->hasErrors())->toBeTrue()
        ->and($preview->errors->totalCount)->toBe(1)
        ->and(componentImportCounts($preview))->toBe(['created' => 0, 'updated' => 0, 'unchanged' => 0]);
});
