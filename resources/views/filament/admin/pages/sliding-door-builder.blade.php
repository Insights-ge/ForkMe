<x-filament-panels::page>
    @php
        $steps = [
            1 => __('sliding_door_builder.steps.opening'),
            2 => __('sliding_door_builder.steps.system'),
            3 => __('sliding_door_builder.steps.panels'),
            4 => __('sliding_door_builder.steps.review'),
        ];
    @endphp

    <div x-data="{ activeStep: 1 }" class="space-y-6">
        <x-sliding-door-builder.step-navigation :steps="$steps" />

        <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-2">
            <x-sliding-door-builder.controls :steps="$steps" />

            <div class="space-y-6">
                <x-sliding-door-builder.preview />
                <x-sliding-door-builder.summary />
            </div>
        </div>
    </div>
</x-filament-panels::page>
