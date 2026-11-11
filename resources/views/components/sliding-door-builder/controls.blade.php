@props(['steps'])

<div>
    @foreach ($steps as $number => $step)
        <div
            id="builder-step-{{ $number }}"
            x-show="activeStep === {{ $number }}"
            @if ($number !== 1) x-cloak @endif
        >
            <x-filament::section :heading="$step['label']">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $step['description'] }}
                </p>
            </x-filament::section>
        </div>
    @endforeach
</div>
