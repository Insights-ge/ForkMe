<?php

namespace App\Services;

use App\Data\ComponentImportRowData;
use App\Data\ImportErrorListData;
use App\Enums\ComponentImportColumn as Column;
use App\Enums\ComponentType;
use App\Enums\DoorColor;
use App\Support\ImportErrorCollector;

/**
 * Checks component spreadsheet rows against the import rules.
 *
 * Saved components aren't compared: the file wins and updates them. Not checked: the format of codes,
 * and whether the same unique code under different types has matching values.
 */
class ComponentImportValidator
{
    public const int MAX_TEXT_LENGTH = 255;

    /**
     * @param  list<ComponentImportRowData>  $rows
     */
    public function validate(array $rows): ImportErrorListData
    {
        $errors = new ImportErrorCollector;

        /** @var array<string, int> $firstRows First row of each unique code + type in the file. */
        $firstRows = [];

        foreach ($rows as $row) {
            $type = $this->validateType($row, $errors);
            $hasUniqueCode = $this->validateText($row->rowNumber, Column::UniqueCode, $row->uniqueCode, $errors);
            $this->validateText($row->rowNumber, Column::Code, $row->code, $errors);
            $this->validateText($row->rowNumber, Column::Description, $row->description, $errors);
            $this->validateColor($row, $errors);

            if ($type === null || ! $hasUniqueCode) {
                continue;
            }

            $key = "{$type->value}|{$row->uniqueCode}";

            if (isset($firstRows[$key])) {
                $errors->add($row->rowNumber, Column::UniqueCode->value, __('components.import.errors.duplicate_in_file', [
                    'unique_code' => $row->uniqueCode,
                    'type' => $row->type,
                    'row' => $firstRows[$key],
                ]));

                continue;
            }

            $firstRows[$key] = $row->rowNumber;
        }

        return $errors->toData();
    }

    private function validateType(ComponentImportRowData $row, ImportErrorCollector $errors): ?ComponentType
    {
        if ($row->type === '') {
            $errors->add($row->rowNumber, Column::Type->value, __('components.import.errors.required', ['column' => Column::Type->value]));

            return null;
        }

        $type = ComponentType::tryFromGeorgian($row->type);

        if ($type === null) {
            $allowed = implode(', ', array_map(fn (ComponentType $type): string => $type->georgianName(), ComponentType::cases()));
            $errors->add($row->rowNumber, Column::Type->value, __('components.import.errors.invalid_type', ['value' => $row->type, 'allowed' => $allowed]));
        }

        return $type;
    }

    /**
     * The colour is optional on every type; when filled in, it must be a Georgian colour name.
     */
    private function validateColor(ComponentImportRowData $row, ImportErrorCollector $errors): void
    {
        if ($row->color === '' || DoorColor::tryFromGeorgian($row->color) !== null) {
            return;
        }

        $allowed = implode(', ', array_map(fn (DoorColor $color): string => $color->georgianName(), DoorColor::cases()));
        $errors->add($row->rowNumber, Column::Color->value, __('components.import.errors.invalid_color', ['value' => $row->color, 'allowed' => $allowed]));
    }

    /**
     * @return bool Whether the value is filled in and short enough.
     */
    private function validateText(int $rowNumber, Column $column, string $value, ImportErrorCollector $errors): bool
    {
        if ($value === '') {
            $errors->add($rowNumber, $column->value, __('components.import.errors.required', ['column' => $column->value]));

            return false;
        }

        if (mb_strlen($value) > self::MAX_TEXT_LENGTH) {
            $errors->add($rowNumber, $column->value, __('components.import.errors.too_long', ['column' => $column->value, 'max' => self::MAX_TEXT_LENGTH]));

            return false;
        }

        return true;
    }
}
