<?php

namespace App\Data;

use App\Enums\DoorColor;

readonly class BuilderFrameColorData
{
    public function __construct(
        public DoorColor $color,
        public string $label,
        public BuilderVisualData $visual,
    ) {}
}
