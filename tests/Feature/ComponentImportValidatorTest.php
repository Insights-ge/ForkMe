<?php

use App\Data\ComponentImportRowData;
use App\Data\ImportErrorListData;
use App\Data\ImportRowErrorData;
use App\Enums\ComponentType;
use App\Enums\DoorColor;
use App\Services\ComponentImportValidator;

/**
 * @param  list<ComponentImportRowData>  $rows
 */
function validateComponentRows(array $rows): ImportErrorListData
{
    return app(ComponentImportValidator::class)->validate($rows);
}

/**
 * @return list<string> Each error as "row|column|message".
 */
function componentValidationErrors(ImportErrorListData $result): array
{
    return array_map(fn (ImportRowErrorData $error): string => "{$error->rowNumber}|{$error->column}|{$error->message}", $result->errors);
}

test('a valid row has no errors', function () {
    expect(validateComponentRows([componentImportRowData()])->hasErrors())->toBeFalse();
});

test('required fields must be filled in', function (string $field, string $column) {
    $result = validateComponentRows([componentImportRowData([$field => ''])]);

    expect(componentValidationErrors($result))->toBe(["2|{$column}|" . __('components.import.errors.required', ['column' => $column])]);
})->with([
    ['type', 'type'],
    ['uniqueCode', 'unique_code'],
    ['code', 'code'],
    ['description', 'description'],
]);

test('text longer than 255 characters is rejected', function (string $field, string $column) {
    $result = validateComponentRows([componentImportRowData([$field => str_repeat('ა', 256)])]);

    expect(componentValidationErrors($result))->toBe(["2|{$column}|" . __('components.import.errors.too_long', ['column' => $column, 'max' => 255])]);
})->with([
    ['uniqueCode', 'unique_code'],
    ['code', 'code'],
    ['description', 'description'],
]);

test('text of exactly 255 characters is accepted', function () {
    $text = str_repeat('ა', 255);

    expect(validateComponentRows([componentImportRowData(['uniqueCode' => $text, 'code' => $text, 'description' => $text])])->hasErrors())->toBeFalse();
});

test('every georgian type name is accepted', function (ComponentType $type) {
    expect(validateComponentRows([componentImportRowData(['type' => $type->georgianName()])])->hasErrors())->toBeFalse();
})->with(ComponentType::cases());

test('a type that is not a georgian type name is rejected', function (string $type) {
    $result = validateComponentRows([componentImportRowData(['type' => $type])]);

    expect(componentValidationErrors($result))->toBe(['2|type|' . __('components.import.errors.invalid_type', [
        'value' => $type,
        'allowed' => 'პროფილი გვერდითა, შემკვრელი ზედა, შემკვრელი ქვედა, ტიხარი, ნელი დაკეტვის მექანიზმი',
    ])]);
})->with(['side_profile', 'Side profile', 'ტიხარ']);

test('every georgian colour name is accepted', function (DoorColor $color) {
    expect(validateComponentRows([componentImportRowData(['color' => $color->georgianName()])])->hasErrors())->toBeFalse();
})->with(DoorColor::cases());

test('the colour is optional on every type, and any type may have one', function (ComponentType $type, string $color) {
    expect(validateComponentRows([componentImportRowData(['type' => $type->georgianName(), 'color' => $color])])->hasErrors())->toBeFalse();
})->with(ComponentType::cases())->with(['', 'შავი']);

test('a colour that is not a georgian colour name is rejected', function (string $color) {
    $result = validateComponentRows([componentImportRowData(['color' => $color])]);

    expect(componentValidationErrors($result))->toBe(['2|color|' . __('components.import.errors.invalid_color', [
        'value' => $color,
        'allowed' => 'შავი, ინოქსი, გრაფიტი, სილვერი',
    ])]);
})->with(['black', 'ინოქს']);

test('the same unique code and type may appear only once in the file', function () {
    $result = validateComponentRows([
        componentImportRowData(),
        componentImportRowData(['rowNumber' => 3, 'description' => 'სხვა აღწერა']),
    ]);

    expect(componentValidationErrors($result))->toBe(['3|unique_code|' . __('components.import.errors.duplicate_in_file', [
        'unique_code' => '000015193',
        'type' => 'პროფილი გვერდითა',
        'row' => 2,
    ])]);
});

test('the same unique code with different types is allowed and the values may differ', function () {
    $result = validateComponentRows([
        componentImportRowData(['type' => 'შემკვრელი ზედა']),
        componentImportRowData(['rowNumber' => 3, 'type' => 'ტიხარი', 'code' => 'სხვა', 'description' => 'სხვა აღწერა', 'color' => 'ინოქსი']),
    ]);

    expect($result->hasErrors())->toBeFalse();
});

test('codes are not checked for a format', function () {
    expect(validateComponentRows([componentImportRowData(['uniqueCode' => '15193', 'code' => 'any text'])])->hasErrors())->toBeFalse();
});

test('all errors of a row are reported', function () {
    $result = validateComponentRows([componentImportRowData(['type' => 'x', 'uniqueCode' => '', 'code' => '', 'description' => '', 'color' => 'x'])]);

    expect(array_map(fn (ImportRowErrorData $error): ?string => $error->column, $result->errors))
        ->toBe(['type', 'unique_code', 'code', 'description', 'color']);
});

test('only the first 200 errors are kept but all of them are counted', function () {
    $rows = array_map(fn (int $index): ComponentImportRowData => componentImportRowData(['rowNumber' => $index, 'type' => 'x']), range(2, 251));

    $result = validateComponentRows($rows);

    expect($result->errors)->toHaveCount(200)
        ->and($result->totalCount)->toBe(250)
        ->and($result->errors[0]->rowNumber)->toBe(2)
        ->and($result->errors[199]->rowNumber)->toBe(201);
});
