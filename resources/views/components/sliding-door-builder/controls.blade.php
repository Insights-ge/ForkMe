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
            </x-filament::section>
        </div>
    @endforeach
</div>
