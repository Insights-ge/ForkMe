<x-filament-panels::page>
    <div x-data="{ activeStep: {{ $initialStep->value }} }" class="space-y-6">
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
