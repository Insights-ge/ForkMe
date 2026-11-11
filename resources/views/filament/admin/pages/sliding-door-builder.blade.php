<x-filament-panels::page>
    <div
        x-load
        x-load-src="{{ \Illuminate\Support\Facades\Vite::asset('resources/js/sliding-door-builder.js') }}"
        x-data="slidingDoorBuilder({ initialStep: {{ $initialStep->value }} })"
        class="sliding-door-builder grid gap-6"
    >
        <x-sliding-door-builder.step-navigation :steps="$steps" />

        <div class="builder-workspace grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1.35fr)]">
            <x-sliding-door-builder.controls :steps="$steps" :initial-step="$initialStep" />

            <div class="builder-design grid items-start gap-6">
                <x-sliding-door-builder.preview />
                <x-sliding-door-builder.summary />
            </div>
        </div>
    </div>
</x-filament-panels::page>
