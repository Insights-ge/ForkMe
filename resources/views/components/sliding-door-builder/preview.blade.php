<x-filament::section :heading="__('sliding_door_builder.preview.title')" class="builder-preview">
    <div x-show="hasPreview" x-cloak class="builder-preview-selectors space-y-3 mb-4">
        <div role="group" aria-label="{{ __('sliding_door_builder.preview.doors_label') }}" class="flex flex-wrap gap-2">
            <template x-for="door in previewDoors" :key="door.id">
                <x-filament::button
                    color="gray"
                    size="sm"
                    class="builder-preview-selector"
                    x-on:click="selectPreviewDoor(door.id)"
                    x-bind:aria-pressed="previewSelection.doorId === door.id"
                >
                    <span x-text="previewDoorLabel(door.index)"></span>
                </x-filament::button>
            </template>
        </div>
        <div
            x-show="selectedPreviewDoor !== null"
            role="group"
            aria-label="{{ __('sliding_door_builder.preview.panels_label') }}"
            class="flex flex-wrap gap-2"
        >
            <template x-for="panel in (selectedPreviewDoor?.panels ?? [])" :key="panel.id">
                <x-filament::button
                    color="gray"
                    size="sm"
                    class="builder-preview-selector"
                    x-on:click="selectPreviewPanel(selectedPreviewDoor.id, panel.id)"
                    x-bind:aria-pressed="previewSelection.panelId === panel.id"
                >
                    <span x-text="previewPanelLabel(panel.index)"></span>
                </x-filament::button>
            </template>
        </div>
    </div>
    <div class="builder-preview-canvas">
        <svg
            x-show="hasPreview"
            x-cloak
            role="group"
            aria-label="{{ __('sliding_door_builder.preview.drawing_label') }}"
            class="builder-preview-svg"
            wire:ignore
        ></svg>
        <div x-show="!hasPreview" class="builder-preview-placeholder">
            <x-filament::empty-state
                :heading="__('sliding_door_builder.preview.empty_heading')"
                :description="__('sliding_door_builder.preview.empty_description')"
                :icon="\Filament\Support\Icons\Heroicon::OutlinedPhoto"
                :contained="false"
            />
        </div>
    </div>
</x-filament::section>
