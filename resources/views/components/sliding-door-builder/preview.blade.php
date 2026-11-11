<x-filament::section :heading="__('sliding_door_builder.preview.title')" class="builder-preview">
    <div class="builder-preview-canvas">
        <svg
            x-show="hasPreview"
            x-cloak
            role="img"
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
