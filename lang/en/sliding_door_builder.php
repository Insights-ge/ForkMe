<?php

return [
    'title' => 'Sliding door builder',
    'navigation_label' => 'Builder',
    'steps_label' => 'Door builder steps',
    'catalog' => [
        'no_models_heading' => 'No door systems available',
        'no_models_description' => 'There are currently no door systems available to configure.',
        'no_infills_heading' => 'No panel finishes available',
        'no_infills_description' => 'Panel finishes are currently unavailable. Please try again later.',
    ],
    'layouts' => [
        'full_panel' => 'Full panel',
        'two_horizontal_panels' => 'Two equal horizontal panels',
        'three_horizontal_panels' => 'Three equal horizontal panels',
    ],
    'steps' => [
        'opening' => [
            'label' => 'Size',
            'description' => 'Set the width and height of the opening.',
        ],
        'system' => [
            'label' => 'Door system and colour',
            'description' => 'Choose the door system and frame colour.',
        ],
        'panels' => [
            'label' => 'Layouts and finishes',
            'description' => 'Choose a layout and panel finishes for each door.',
        ],
        'review' => [
            'label' => 'Review',
            'description' => 'Review the measurements and selections for your doors.',
        ],
    ],
    'preview' => [
        'title' => 'Door preview',
        'drawing_label' => 'Sliding door front view with width and height dimensions',
        'width_annotation' => 'Width: :value mm',
        'height_annotation' => 'Height: :value mm',
        'empty_heading' => 'No preview yet',
        'empty_description' => 'Your door design will appear here.',
    ],
    'summary' => [
        'title' => 'Design summary',
        'empty_heading' => 'No selections yet',
        'empty_description' => 'Your measurements and door selections will appear here.',
    ],
];
