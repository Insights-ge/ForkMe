<x-filament-panels::page>
    <div
        x-load
        x-load-src="{{ \Illuminate\Support\Facades\Vite::asset('resources/js/sliding-door-builder.js') }}"
        x-data="slidingDoorBuilder({ initialStep: {{ $initialStep->value }} })"
        class="space-y-6"
    >
        <x-sliding-door-builder.step-navigation :steps="$steps" />

        <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-2">
            <x-sliding-door-builder.controls :steps="$steps" :initial-step="$initialStep" />

            <div class="space-y-6">
                <x-sliding-door-builder.preview />
                <x-sliding-door-builder.summary />
            </div>
        </div>
    </div>
</x-filament-panels::page>
