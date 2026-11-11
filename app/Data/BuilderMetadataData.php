<?php

namespace App\Data;

readonly class BuilderMetadataData
{
    /**
     * @param  list<BuilderLayoutPresetData>  $layoutPresets
     */
    public function __construct(
        public array $layoutPresets,
    ) {}
}
