<?php

namespace App\Data;

use App\Enums\DoorColor;

readonly class BuilderDoorVariantData
{
    /**
     * Components identify their role by type, including optional partitions and soft close.
     *
     * @param  list<BuilderComponentCompatibilityData>  $components
     */
    public function __construct(
        public int $id,
        public string $name,
        public DoorColor $color,
        public string $frameDesignCode,
        public int $heightRangeMin,
        public int $heightRangeMax,
        public array $components,
    ) {}
}
