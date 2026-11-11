<x-filament-panels::page>
    <div
        x-load
        x-load-src="{{ \Illuminate\Support\Facades\Vite::asset('resources/js/sliding-door-builder.js') }}"
        x-data="slidingDoorBuilder({ initialStep: {{ $initialStep->value }} })"
        class="sliding-door-builder grid grid-cols-1 gap-4 sm:gap-6"
    >
        <x-sliding-door-builder.step-navigation :steps="$steps" />

        <div class="builder-workspace grid items-start gap-4 sm:gap-6">
            <x-sliding-door-builder.controls :steps="$steps" :initial-step="$initialStep" />
            <x-sliding-door-builder.preview />
            <x-sliding-door-builder.summary />
        </div>
    </div>
</x-filament-panels::page>
