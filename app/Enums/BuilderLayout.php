<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum BuilderLayout: string implements HasLabel
{
    case FullPanel = 'full_panel';
    case TwoHorizontalPanels = 'two_horizontal_panels';
    case ThreeHorizontalPanels = 'three_horizontal_panels';

    public function getLabel(): string
    {
        return __('sliding_door_builder.layouts.' . $this->value);
    }

    /**
     * Relative panel heights, ordered from top to bottom.
     *
     * @return list<int>
     */
    public function panelWeights(): array
    {
        return match ($this) {
            self::FullPanel => [1],
            self::TwoHorizontalPanels => [1, 1],
            self::ThreeHorizontalPanels => [1, 1, 1],
        };
    }
}
