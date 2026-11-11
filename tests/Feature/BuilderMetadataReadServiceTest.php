<?php

use App\Enums\DoorColor;
use App\Models\Infill;
use App\Services\BuilderCatalogReadService;
use App\Services\BuilderMetadataReadService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('every catalog frame colour has a visual and a translated label', function (string $locale) {
    app()->setLocale($locale);
    $metadata = app(BuilderMetadataReadService::class)->read();

    expect(array_column($metadata->frameColors, 'color'))->toBe(DoorColor::cases());
    foreach ($metadata->frameColors as $frame) {
        expect($frame->label)->toBe($frame->color->getLabel());
        expect($frame->visual->fill)->toMatch('/\A#[0-9A-F]{6}\z/');
    }
    expect($metadata->infillVisuals)->toBe([]);
})->with(['en', 'ka']);

test('missing and invalid frame mappings use the configured neutral visual', function () {
    config([
        'builder.neutral_fill' => '#abcdef',
        'builder.frame_colors' => ['black' => 'url(invalid)', 'silver' => '#123456'],
    ]);

    $metadata = app(BuilderMetadataReadService::class)->read();

    expect($metadata->neutralVisual->fill)->toBe('#ABCDEF');
    foreach ($metadata->frameColors as $frame) {
        expect($frame->visual->fill)->toBe($frame->color === DoorColor::Silver ? '#123456' : '#ABCDEF');
    }
});

test('an invalid neutral colour falls back to a valid grey', function () {
    config(['builder.neutral_fill' => null, 'builder.frame_colors' => []]);

    $metadata = app(BuilderMetadataReadService::class)->read();

    expect($metadata->neutralVisual->fill)->toBe('#B8B8B8');
    foreach ($metadata->frameColors as $frame) {
        expect($frame->visual->fill)->toBe('#B8B8B8');
    }
});

test('optional infill mappings use IDs and never alter catalog records', function () {
    $mapped = Infill::factory()->create();
    $invalid = Infill::factory()->create();
    $unmapped = Infill::factory()->create();
    config(['builder.infill_visuals' => [
        $mapped->id => '#aabbcc',
        $invalid->id => ['fill' => 'invalid'],
        -1 => '#123456',
        'description' => '#123456',
    ]]);
    $catalogBefore = json_encode(app(BuilderCatalogReadService::class)->read(), JSON_THROW_ON_ERROR);

    $metadata = app(BuilderMetadataReadService::class)->read();
    $visuals = collect($metadata->infillVisuals)->keyBy('infillId');

    expect($visuals->keys()->all())->toBe([$mapped->id, $invalid->id]);
    expect($visuals[$mapped->id]->visual->fill)->toBe('#AABBCC');
    expect($visuals[$invalid->id]->visual->fill)->toBe($metadata->neutralVisual->fill);
    expect($visuals->has($unmapped->id))->toBeFalse();
    expect(json_encode(app(BuilderCatalogReadService::class)->read(), JSON_THROW_ON_ERROR))->toBe($catalogBefore);
});

test('malformed infill mapping configuration is treated as unmapped', function () {
    config(['builder.infill_visuals' => 'invalid']);

    expect(app(BuilderMetadataReadService::class)->read()->infillVisuals)->toBe([]);
});
