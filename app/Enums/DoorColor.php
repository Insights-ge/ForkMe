<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DoorColor: string implements HasColor, HasLabel
{
    case Black = 'black';
    case Inox = 'inox';
    case Graphite = 'graphite';
    case Silver = 'silver';

    /**
     * Maps the Georgian colour name used in import spreadsheets back to the enum.
     */
    public static function tryFromGeorgian(string $name): ?self
    {
        foreach (self::cases() as $color) {
            if ($color->georgianName() === $name) {
                return $color;
            }
        }

        return null;
    }

    /**
     * The fixed Georgian colour name used in import spreadsheets. Unlike the label, it isn't translatable,
     * so editing translations doesn't change which files can be imported.
     */
    public function georgianName(): string
    {
        return match ($this) {
            self::Black => 'შავი',
            self::Inox => 'ინოქსი',
            self::Graphite => 'გრაფიტი',
            self::Silver => 'სილვერი',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Black => __('catalog.colors.black'),
            self::Inox => __('catalog.colors.inox'),
            self::Graphite => __('catalog.colors.graphite'),
            self::Silver => __('catalog.colors.silver'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Black => 'gray',
            self::Inox => 'blue',
            self::Graphite => 'purple',
            self::Silver => 'yellow',
        };
    }
}
