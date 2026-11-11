<?php

use App\Enums\DoorColor;

return [
    // Illustrative rendering colours, independent of catalog and admin badge colours.
    'neutral_fill' => '#B8B8B8',

    'frame_colors' => [
        DoorColor::Black->value => '#202020',
        DoorColor::Inox->value => '#A4A6A5',
        DoorColor::Graphite->value => '#4A4D4F',
        DoorColor::Silver->value => '#C5C7C9',
    ],

    // Optional mappings: positive infill database ID => six-digit hex colour.
    // Unmapped infills use neutral_fill; never infer finishes from descriptions.
    'infill_visuals' => [],
];
