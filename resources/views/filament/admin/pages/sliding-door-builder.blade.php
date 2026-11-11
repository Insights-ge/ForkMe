<x-filament-panels::page>
    <div
        x-load
        x-load-src="{{ \Illuminate\Support\Facades\Vite::asset('resources/js/sliding-door-builder.js') }}"
        x-data="slidingDoorBuilder({ initialStep: {{ $initialStep->value }}, payload: @js($builderPayload), previewLabels: @js(__('sliding_door_builder.preview')) })"
        class="sliding-door-builder grid grid-cols-1 gap-4 sm:gap-6"
    >
        @if ($builderPayload->catalog->models === [])
            <x-filament::empty-state
                :heading="__('sliding_door_builder.catalog.no_models_heading')"
                :description="__('sliding_door_builder.catalog.no_models_description')"
            />
        @elseif ($builderPayload->catalog->infills === [])
            <x-filament::empty-state
                :heading="__('sliding_door_builder.catalog.no_infills_heading')"
                :description="__('sliding_door_builder.catalog.no_infills_description')"
            />
        @else
            <x-sliding-door-builder.step-navigation :steps="$steps" />

            <div class="builder-workspace grid items-start gap-4 sm:gap-6">
                <x-sliding-door-builder.controls :steps="$steps" :initial-step="$initialStep" />
                <x-sliding-door-builder.preview />
                <x-sliding-door-builder.summary />
            </div>
        @endif
    </div>
</x-filament-panels::page>
