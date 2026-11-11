<?php

namespace App\Data;

use App\Enums\BuilderLayout;

readonly class BuilderLayoutPresetData
{
    public bool $requiresPartition;

    /**
     * @param  list<int>  $panelWeights  Relative heights, ordered from top to bottom.
     */
    public function __construct(
        public BuilderLayout $id,
        public string $label,
        public array $panelWeights,
    ) {
        $this->requiresPartition = count($panelWeights) > 1;
    }
}
