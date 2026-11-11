<?php

namespace App\Services;

use App\Data\BuilderLayoutPresetData;
use App\Data\BuilderMetadataData;
use App\Enums\BuilderLayout;

class BuilderMetadataReadService
{
    public function read(): BuilderMetadataData
    {
        return new BuilderMetadataData(
            layoutPresets: array_map(
                fn (BuilderLayout $layout): BuilderLayoutPresetData => new BuilderLayoutPresetData(
                    id: $layout,
                    label: $layout->getLabel(),
                    panelWeights: $layout->panelWeights(),
                ),
                BuilderLayout::cases(),
            ),
        );
    }
}
