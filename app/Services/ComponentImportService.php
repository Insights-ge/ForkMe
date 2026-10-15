<?php

namespace App\Services;

use App\Data\ComponentImportPreviewData;
use App\Data\ComponentImportResultData;
use App\Data\ComponentImportRowData;
use App\Data\ImportErrorListData;
use App\Enums\ComponentType;
use App\Enums\DoorColor;
use App\Models\Component;

/**
 * Previews and imports validated component spreadsheet rows.
 *
 * Components are matched by unique code + type and the file wins: matching components are overwritten,
 * the rest are created. Components that aren't in the file are never changed.
 */
class ComponentImportService
{
    private const int CHUNK_SIZE = 1000;

    public function __construct(private readonly ComponentImportValidator $validator) {}

    /**
     * Counts what an import would do, without saving anything.
     *
     * @param  list<ComponentImportRowData>  $rows
     */
    public function preview(array $rows): ComponentImportPreviewData
    {
        $errors = $this->validator->validate($rows);

        if ($errors->hasErrors()) {
            return new ComponentImportPreviewData(0, 0, 0, $errors);
        }

        [$toCreateCount, $toUpdateCount, $unchangedCount] = [0, 0, 0];

        foreach ($this->filledComponents($rows) as $component) {
            match (true) {
                ! $component->exists => $toCreateCount++,
                $component->isDirty() => $toUpdateCount++,
                default => $unchangedCount++,
            };
        }

        return new ComponentImportPreviewData($toCreateCount, $toUpdateCount, $unchangedCount, $errors);
    }

    /**
     * Validates the rows again and saves them in one transaction. Nothing is saved when there is any error.
     *
     * @param  list<ComponentImportRowData>  $rows
     */
    public function import(array $rows): ComponentImportResultData
    {
        $errors = $this->validator->validate($rows);

        if ($errors->hasErrors()) {
            return new ComponentImportResultData(0, 0, 0, $errors);
        }

        return Component::query()->getConnection()->transaction(fn (): ComponentImportResultData => $this->save($rows, $errors));
    }

    /**
     * @param  list<ComponentImportRowData>  $rows
     */
    private function save(array $rows, ImportErrorListData $errors): ComponentImportResultData
    {
        [$createdCount, $updatedCount, $unchangedCount] = [0, 0, 0];

        foreach ($this->filledComponents($rows) as $component) {
            match (true) {
                ! $component->exists => $createdCount++,
                $component->isDirty() => $updatedCount++,
                default => $unchangedCount++,
            };

            $component->save();
        }

        return new ComponentImportResultData($createdCount, $updatedCount, $unchangedCount, $errors);
    }

    /**
     * The components of the rows, filled with the row values but not saved: new ones don't exist yet,
     * changed ones are dirty.
     *
     * @param  list<ComponentImportRowData>  $rows
     * @return list<Component>
     */
    private function filledComponents(array $rows): array
    {
        $savedComponents = $this->savedComponents($rows);

        return array_map(function (ComponentImportRowData $row) use ($savedComponents): Component {
            $type = ComponentType::tryFromGeorgian($row->type);
            $component = $savedComponents[$this->key($type, $row->uniqueCode)] ?? new Component(['type' => $type, 'unique_code' => $row->uniqueCode]);

            return $component->fill([
                'code' => $row->code,
                'description' => $row->description,
                'color' => $row->color === '' ? null : DoorColor::tryFromGeorgian($row->color),
            ]);
        }, $rows);
    }

    /**
     * The saved components with the unique codes of the rows, keyed by type + unique code.
     *
     * @param  list<ComponentImportRowData>  $rows
     * @return array<string, Component>
     */
    private function savedComponents(array $rows): array
    {
        $savedComponents = [];

        foreach (array_chunk(array_unique(array_column($rows, 'uniqueCode')), self::CHUNK_SIZE) as $uniqueCodes) {
            foreach (Component::query()->whereIn('unique_code', $uniqueCodes)->get() as $component) {
                $savedComponents[$this->key($component->type, $component->unique_code)] = $component;
            }
        }

        return $savedComponents;
    }

    private function key(?ComponentType $type, string $uniqueCode): string
    {
        return "{$type?->value}|{$uniqueCode}";
    }
}
