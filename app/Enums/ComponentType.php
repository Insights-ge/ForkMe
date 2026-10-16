<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ComponentType: string implements HasColor, HasLabel
{
    case SideProfile = 'side_profile';
    case TopRail = 'top_rail';
    case BottomRail = 'bottom_rail';
    case Partition = 'partition';
    case SoftCloseMechanism = 'soft_close_mechanism';

    /**
     * Maps the Georgian type name used in import spreadsheets back to the enum.
     */
    public static function tryFromGeorgian(string $name): ?self
    {
        foreach (self::cases() as $type) {
            if ($type->georgianName() === $name) {
                return $type;
            }
        }

        return null;
    }

    /**
     * The fixed Georgian type name used in import spreadsheets. Unlike the label, it isn't translatable,
     * so editing translations doesn't change which files can be imported.
     */
    public function georgianName(): string
    {
        return match ($this) {
            self::SideProfile => 'პროფილი გვერდითა',
            self::TopRail => 'შემკვრელი ზედა',
            self::BottomRail => 'შემკვრელი ქვედა',
            self::Partition => 'ტიხარი',
            self::SoftCloseMechanism => 'ნელი დაკეტვის მექანიზმი',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::SideProfile => __('catalog.component_types.side_profile'),
            self::TopRail => __('catalog.component_types.top_rail'),
            self::BottomRail => __('catalog.component_types.bottom_rail'),
            self::Partition => __('catalog.component_types.partition'),
            self::SoftCloseMechanism => __('catalog.component_types.soft_close_mechanism'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SideProfile => 'info',
            self::TopRail => 'success',
            self::BottomRail => 'warning',
            self::Partition => 'purple',
            self::SoftCloseMechanism => 'gray',
        };
    }
}
