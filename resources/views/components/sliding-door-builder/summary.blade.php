<x-filament::section :heading="__('sliding_door_builder.summary.title')" class="builder-summary">
    <x-filament::empty-state
        :heading="__('sliding_door_builder.summary.empty_heading')"
        :description="__('sliding_door_builder.summary.empty_description')"
        :icon="\Filament\Support\Icons\Heroicon::OutlinedClipboardDocumentList"
        :contained="false"
        compact
    />
</x-filament::section>
