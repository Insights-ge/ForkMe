@props(['steps'])

<div class="overflow-x-auto">
    <x-filament::tabs :label="__('sliding_door_builder.steps_label')" class="min-w-max">
        @foreach ($steps as $number => $step)
            <x-filament::tabs.item
                :badge="$number"
                :alpine-active="'activeStep === ' . $number"
                x-on:click="activeStep = {{ $number }}"
                x-bind:aria-current="activeStep === {{ $number }} ? 'step' : null"
                aria-controls="builder-step-{{ $number }}"
            >
                {{ $step['label'] }}
            </x-filament::tabs.item>
        @endforeach
    </x-filament::tabs>
</div>
