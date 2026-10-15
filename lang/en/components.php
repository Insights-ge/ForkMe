<?php

return [
    'layout' => [
        'components' => 'Components',
        'create_component' => 'Create Component',
        'edit_component' => 'Edit Component',
    ],
    'table' => [
        'type' => 'Type',
        'code' => 'Code',
        'description' => 'Description',
        'color' => 'Color',
        'unique_code' => 'Unique code',
        'empty_heading' => 'No components found',
        'empty_description' => 'Components are seeded from the catalog import.',
        'delete_in_use_title' => 'Component is in use',
        'delete_in_use_body' => 'This component is used by one or more door variants and cannot be deleted.',
    ],
    'form' => [
        'type' => 'Type',
        'code' => 'Code',
        'description' => 'Description',
        'color' => 'Color',
        'unique_code' => 'Unique code',
    ],
    'import' => [
        'errors' => [
            'file_unreadable' => 'The file could not be read. Upload a valid .xlsx file.',
            'no_header' => 'The file has no header row.',
            'missing_column' => 'Column :column is missing.',
            'unknown_column' => 'Column :column is not a known column.',
            'duplicate_column' => 'Column :column appears more than once.',
            'too_many_rows' => 'The file has more than :max rows.',
        ],
    ],
];
