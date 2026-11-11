@props(['steps', 'initialStep'])

<div class="builder-controls">
    @foreach ($steps as $step)
        <div
            id="builder-step-{{ $step->value }}"
            x-show="activeStep === {{ $step->value }}"
            @if ($step !== $initialStep) x-cloak @endif
        >
            <x-filament::section :heading="$step->getLabel()">
                <p class="builder-control-description text-sm text-gray-500 dark:text-gray-400">
                    {{ $step->getDescription() }}
                </p>
                @if ($step === \App\Enums\SlidingDoorBuilderStep::Panels)
                    <fieldset class="mt-4" x-bind:disabled="!hasPreview">
                        <legend class="text-sm font-medium text-gray-950 dark:text-white">
                            {{ __('sliding_door_builder.layouts.title') }}
                        </legend>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('sliding_door_builder.layouts.shared_description') }}
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <template x-for="preset in metadata.layoutPresets" :key="preset.id">
                                <x-filament::button
                                    color="gray"
                                    class="builder-preview-selector"
                                    x-on:click="selectLayout(preset.id)"
                                    x-bind:aria-pressed="selectedLayoutId === preset.id"
                                    x-bind:data-layout-preset="preset.id"
                                >
                                    <span x-text="preset.label"></span>
                                </x-filament::button>
                            </template>
                        </div>
                    </fieldset>
                @endif
            </x-filament::section>
        </div>
    @endforeach
</div>
